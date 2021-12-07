<?php

declare(strict_types=1);

use Toddy15\SpamDetect\Models\Token;
use Toddy15\SpamDetect\SpamDetect;

it('can instantiate a spam detector', function () {
    $spamdetect = new SpamDetect();
    expect($spamdetect)->toBeInstanceOf(SpamDetect::class);
});

it('classifies a text with an empty database as 0.5', function () {
    $spamdetect = new SpamDetect();
    expect($spamdetect->classify('This text should rate with 0.5'))->toBe(0.5);
});

it('can train with a text known to be ham', function () {
    $spamdetect = new SpamDetect();

    // Ensure the database has no training texts yet
    $stats = Token::find(1);
    expect($stats->count_ham)->toBe(0);
    expect($stats->count_spam)->toBe(0);

    // Ensure the database contains no tokens
    expect(
        Token::where('id', '>', 1)->count()
    )->toBe(0);

    $spamdetect->trainHam('This text is ham');

    // Ensure the database now has one training text
    $stats->refresh();
    expect($stats->count_ham)->toBe(1);
    expect($stats->count_spam)->toBe(0);

    // Check if the database contains four tokens
    expect(
        Token::where('id', '>', 1)->count()
    )->toBe(4);

    // Check that all tokens have been added correctly
    foreach (['This', 'text', 'is', 'ham'] as $token) {
        $this->assertDatabaseHas('spamdetect_tokens', [
            'token' => $token,
            'count_ham' => 1,
        ]);
    }
});

it('can train with a text known to be spam', function () {
    $spamdetect = new SpamDetect();

    // Ensure the database has no training texts yet
    $stats = Token::find(1);
    expect($stats->count_ham)->toBe(0);
    expect($stats->count_spam)->toBe(0);

    // Ensure the database contains no tokens
    expect(
        Token::where('id', '>', 1)->count()
    )->toBe(0);

    $spamdetect->trainSpam('This text is spam');

    // Ensure the database now has one training text
    $stats->refresh();
    expect($stats->count_ham)->toBe(0);
    expect($stats->count_spam)->toBe(1);

    // Check if the database contains four tokens
    expect(
        Token::where('id', '>', 1)->count()
    )->toBe(4);

    // Check that all tokens have been added correctly
    foreach (['This', 'text', 'is', 'spam'] as $token) {
        $this->assertDatabaseHas('spamdetect_tokens', [
            'token' => $token,
            'count_spam' => 1,
        ]);
    }
});

it('adds tokens that already exists in the database', function () {
    $spamdetect = new SpamDetect();

    $spamdetect->trainHam('This text is ham');
    $spamdetect->trainHam('This text is ham');

    // Ensure the database now has two training texts
    $stats = Token::find(1);
    expect($stats->count_ham)->toBe(2);
    expect($stats->count_spam)->toBe(0);

    // Check if the database contains four tokens
    expect(
        Token::where('id', '>', 1)->count()
    )->toBe(4);

    // Check that all tokens have been added correctly
    foreach (['This', 'text', 'is', 'ham'] as $token) {
        $this->assertDatabaseHas('spamdetect_tokens', [
            'token' => $token,
            'count_ham' => 2,
        ]);
    }
});

it('can train with texts known to be ham or spam', function () {
    $spamdetect = new SpamDetect();

    $spamdetect->trainHam('This text is ham');
    $spamdetect->trainHam('And this as well');
    $spamdetect->trainSpam('This text is spam');
    $spamdetect->trainSpam('Cheap pills');
    $spamdetect->trainSpam('Cheap pills for you');

    // Ensure the database now has all training texts
    $stats = Token::find(1);
    expect($stats->count_ham)->toBe(2);
    expect($stats->count_spam)->toBe(3);

    // Check if the database contains the correct amount of tokens
    expect(
        Token::where('id', '>', 1)->count()
    )->toBe(13);

    // Check that all tokens have been added correctly
    // Contents of inner array: token, count_ham, count_spam
    $expected_tokens = [
        ['This', 1, 1],
        ['text', 1, 1],
        ['is', 1, 1],
        ['ham', 1, 0],
        ['And', 1, 0],
        ['this', 1, 0],
        ['as', 1, 0],
        ['well', 1, 0],
        ['spam', 0, 1],
        ['Cheap', 0, 2],
        ['pills', 0, 2],
        ['for', 0, 1],
        ['you', 0, 1],
    ];
    foreach ($expected_tokens as $token) {
        $this->assertDatabaseHas('spamdetect_tokens', [
            'token' => $token[0],
            'count_ham' => $token[1],
            'count_spam' => $token[2],
        ]);
    }
});

it('classifies a new ham text based on training data', function () {
    $spamdetect = new SpamDetect();

    $spamdetect->trainHam('This text is ham');

    expect($spamdetect->classify('This'))->toBe(0.01);

    expect($spamdetect->classify('This is an unknown text'))->toBe(0.0);

    expect($spamdetect->classify('No data for evaluation'))->toBe(0.5);
});

it('classifies a new spam text based on training data', function () {
    $spamdetect = new SpamDetect();

    $spamdetect->trainSpam('This text is spam');

    expect($spamdetect->classify('This'))->toBe(0.99);

    expect($spamdetect->classify('This is an unknown text'))->toBe(1.0);

    expect($spamdetect->classify('No data for evaluation'))->toBe(0.5);
});
