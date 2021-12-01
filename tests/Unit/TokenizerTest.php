<?php

declare(strict_types=1);

use Toddy15\NaiveBayes\Tokenizer;

it('splits various input strings into tokens', function (string $input, array $tokens) {
    $tokenizer = new Tokenizer([$input]);
    $result = $tokenizer->tokenize();
    expect($result)->toBe($tokens);
})->with('tokens');
