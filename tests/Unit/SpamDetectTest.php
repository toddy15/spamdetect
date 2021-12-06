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
        ['This', 'unknown', 'cheap']
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
        ['This', 'unknown', 'cheap']
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
        ['This', 'unknown', 'cheap']
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
        ['This', 'unknown', 'cheap']
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
    for ($i = 1; $i <= 20; $i++) {
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
        'word-01' => 0.047619047619047616,
        'word-02' => 0.09090909090909091,
        'word-20' => 0.9090909090909091,
        'word-03' => 0.13043478260869565,
        'word-04' => 0.16666666666666669,
        'word-19' => 0.8260869565217391,
        'word-05' => 0.2,
        'word-06' => 0.23076923076923075,
        'word-18' => 0.75,
        'word-07' => 0.25925925925925924,
        'word-08' => 0.28571428571428575,
        'word-09' => 0.3103448275862069,
        'word-17' => 0.6799999999999999,
        'word-10' => 0.3333333333333333,
        'word-11' => 0.3548387096774194,
    ]);
});
