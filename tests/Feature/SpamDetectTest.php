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
    $result = $spamdetect->classify('This text should rate with 0.5');
    expect($result)->toBe(0.5);
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
