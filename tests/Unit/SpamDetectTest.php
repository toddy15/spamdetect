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
    } catch (Exception $e) {
        // This should not be reached, so make sure the test fails.
        expect(true)->toBeFalse();
    }
    $this->getTokenProbabilities->setAccessible(true);

    $this->spamdetect = new SpamDetect();
    $this->stats = Token::find(1);
});

it('calculates the probability of found tokens without training data', function () {
    $result = $this->getTokenProbabilities->invokeArgs($this->spamdetect, [
        $this->stats,
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
        $this->stats,
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
        $this->stats,
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
        $this->stats,
        ['This', 'unknown', 'cheap']
    ]);
    expect($result)->toBe([
        'This' => 0.01,
        'unknown' => 0.5,
        'cheap' => 0.99,
    ]);
});
