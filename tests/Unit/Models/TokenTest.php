<?php

declare(strict_types=1);

use Toddy15\SpamDetect\Models\Token;

it('can instantiate a new token', function () {
    $token = new Token();
    expect($token)->toBeInstanceOf(Token::class);
});

it('can get the initial token from the database', function () {
    $this->assertDatabaseCount('spamdetect_tokens', 1);
    $token = Token::find(1);
    expect($token)->toBeInstanceOf(Token::class);
    expect($token->token)->toBe('COUNT_OF_HAM_AND_SPAM_TEXTS');
    expect($token->count_ham)->toBe(0);
    expect($token->count_spam)->toBe(0);
});

it('can create a token and persist it to the database', function () {
    $token = Token::factory()->raw();

    $this->assertDatabaseMissing('spamdetect_tokens', $token);

    Token::create($token);

    $this->assertDatabaseHas('spamdetect_tokens', $token);
});

it('can create a token with UTF-8 characters and persist it to the database', function () {
    $token = Token::create([
        'token' => 'This works: ✅',
    ]);

    $this->assertModelExists($token);
});
