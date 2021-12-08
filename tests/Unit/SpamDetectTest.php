<?php

declare(strict_types=1);

use Toddy15\SpamDetect\Models\Token;
use Toddy15\SpamDetect\SpamDetect;

beforeEach(function () {
    // The helper method is private, so make use of the
    // ReflectionClass
    $reflector = new ReflectionClass(SpamDetect::class);

    try {
        $this->getTokenProbabilities = $reflector->getMethod('getTokenProbabilities');
        $this->getImportantTokens = $reflector->getMethod('getImportantTokens');
    } catch (Exception $e) {
        // This should not be reached, so make sure the test fails.
        expect(true)->toBeFalse();
    }
    $this->getTokenProbabilities->setAccessible(true);
    $this->getImportantTokens->setAccessible(true);

    $this->spamdetect = new SpamDetect();
    $this->stats = Token::find(1);
});

it('calculates the probability of found tokens without training data', function () {
    $result = $this->getTokenProbabilities->invokeArgs($this->spamdetect, [
        ['This', 'unknown', 'cheap'],
    ]);
    expect($result)->toBe([
        'This' => 0.5,
        'unknown' => 0.5,
        'cheap' => 0.5,
    ]);
});

it('calculates the probability of found tokens with only ham data', function () {
    $this->spamdetect->trainHam('This text is ham');

    $result = $this->getTokenProbabilities->invokeArgs($this->spamdetect, [
        ['This', 'unknown', 'cheap'],
    ]);
    expect($result)->toBe([
        'This' => 0.01,
        'unknown' => 0.5,
        'cheap' => 0.5,
    ]);
});

it('calculates the probability of found tokens with only spam data', function () {
    $this->spamdetect->trainSpam('Buy cheap pills');

    $result = $this->getTokenProbabilities->invokeArgs($this->spamdetect, [
        ['This', 'unknown', 'cheap'],
    ]);
    expect($result)->toBe([
        'This' => 0.5,
        'unknown' => 0.5,
        'cheap' => 0.99,
    ]);
});

it('calculates the probability of found tokens with ham and spam data', function () {
    $this->spamdetect->trainHam('This text is ham');
    $this->spamdetect->trainSpam('Buy cheap pills');

    $result = $this->getTokenProbabilities->invokeArgs($this->spamdetect, [
        ['This', 'unknown', 'cheap'],
    ]);
    expect($result)->toBe([
        'This' => 0.01,
        'unknown' => 0.5,
        'cheap' => 0.99,
    ]);
});

it('ranks few found tokens according to their importance', function () {
    $this->spamdetect->trainHam('word-01 word-02');
    $this->spamdetect->trainSpam('word-03 word-04');
    $this->stats->refresh();
    $probabilities = $this->getTokenProbabilities->invokeArgs($this->spamdetect, [
        ['word-01', 'other', 'word-04'],
    ]);

    $importantTokens = $this->getImportantTokens->invokeArgs($this->spamdetect, [
        $probabilities,
    ]);

    expect($importantTokens)->toBe([
        'word-01' => 0.01,
        'word-04' => 0.99,
        'other' => 0.5,
    ]);
});

it('ranks found tokens according to their importance', function () {
    // Create an array of 20 different words, then
    // join them together for ham texts like this:
    // word-01
    // word-01 word-02
    // word-01 will now be the most important for ham.
    // For the spam texts, the order is just reversed,
    // so that word-20 will be most important for spam.
    $words = [];
    for ($i = 1; $i <= 20; $i++) {
        $words[] = sprintf('word-%02d', $i);
    }
    // Do not use word-01 in spam and word-20 in ham.
    // Therefore, the upper bound is set to 19 instead of 20.
    for ($i = 1; $i <= 19; $i++) {
        $ham = join(' ', array_slice($words, 0, $i));
        $spam = join(' ', array_slice(array_reverse($words), 0, $i));
        $this->spamdetect->trainHam($ham);
        $this->spamdetect->trainSpam($spam);
    }
    $this->stats->refresh();
    $probabilities = $this->getTokenProbabilities->invokeArgs($this->spamdetect, [
        $words,
    ]);

    $importantTokens = $this->getImportantTokens->invokeArgs($this->spamdetect, [
        $probabilities,
    ]);

    expect($importantTokens)->toBe([
        'word-01' => 0.01,
        'word-20' => 0.99,
        'word-02' => 0.05,
        'word-03' => 0.0952380952381,
        'word-19' => 0.9,
        'word-04' => 0.13636363636364,
        'word-05' => 0.17391304347826,
        'word-18' => 0.80952380952381,
        'word-06' => 0.20833333333333,
        'word-07' => 0.24,
        'word-08' => 0.26923076923077,
        'word-17' => 0.72727272727273,
        'word-09' => 0.2962962962963,
        'word-10' => 0.32142857142857,
        'word-16' => 0.65217391304348,
    ]);
});
