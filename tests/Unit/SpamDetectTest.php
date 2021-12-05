<?php

declare(strict_types=1);

use Toddy15\SpamDetect\Models\Token;
use Toddy15\SpamDetect\SpamDetect;

it('calculates the probability of found tokens without training data', function () {
    // The helper method is private, so make use of the
    // ReflectionClass
    $reflector = new ReflectionClass(SpamDetect::class);
    try {
        $method = $reflector->getMethod('getTokenProbabilities');
    } catch (Exception $e) {
        // This should not be reached, so make sure the test fail.
        expect(true)->toBeFalse();
    }
    $method->setAccessible( true );

    $spamdetect = new SpamDetect();
    $stats = Token::find(1);

    $result = $method->invokeArgs($spamdetect, [
        $stats, ['This', 'unknown', 'cheap']
    ]);
    expect($result)->toBe([
        'This' => 0.5,
        'unknown' => 0.5,
        'cheap' => 0.5,
    ]);
});

it('calculates the probability of found tokens with only ham data', function () {
    // The helper method is private, so make use of the
    // ReflectionClass
    $reflector = new ReflectionClass(SpamDetect::class);
    try {
        $method = $reflector->getMethod('getTokenProbabilities');
    } catch (Exception $e) {
        // This should not be reached, so make sure the test fail.
        expect(true)->toBeFalse();
    }
    $method->setAccessible(true);

    $spamdetect = new SpamDetect();
    $stats = Token::find(1);
    $spamdetect->trainHam('This text is ham');

    $result = $method->invokeArgs($spamdetect, [
        $stats,
        ['This', 'unknown', 'cheap']
    ]);
    expect($result)->toBe([
        'This' => 0.01,
        'unknown' => 0.5,
        'cheap' => 0.5,
    ]);
});

it('calculates the probability of found tokens with only spam data', function () {
    // The helper method is private, so make use of the
    // ReflectionClass
    $reflector = new ReflectionClass(SpamDetect::class);
    try {
        $method = $reflector->getMethod('getTokenProbabilities');
    } catch (Exception $e) {
        // This should not be reached, so make sure the test fail.
        expect(true)->toBeFalse();
    }
    $method->setAccessible(true);

    $spamdetect = new SpamDetect();
    $stats = Token::find(1);
    $spamdetect->trainSpam('Buy cheap pills');

    $result = $method->invokeArgs($spamdetect, [
        $stats,
        ['This', 'unknown', 'cheap']
    ]);
    expect($result)->toBe([
        'This' => 0.5,
        'unknown' => 0.5,
        'cheap' => 0.99,
    ]);
});

it('calculates the probability of found tokens with ham and spam data', function () {
    // The helper method is private, so make use of the
    // ReflectionClass
    $reflector = new ReflectionClass(SpamDetect::class);
    try {
        $method = $reflector->getMethod('getTokenProbabilities');
    } catch (Exception $e) {
        // This should not be reached, so make sure the test fail.
        expect(true)->toBeFalse();
    }
    $method->setAccessible(true);

    $spamdetect = new SpamDetect();
    $stats = Token::find(1);
    $spamdetect->trainHam('This text is ham');
    $spamdetect->trainSpam('Buy cheap pills');

    $result = $method->invokeArgs($spamdetect, [
        $stats,
        ['This', 'unknown', 'cheap']
    ]);
    expect($result)->toBe([
        'This' => 0.01,
        'unknown' => 0.5,
        'cheap' => 0.99,
    ]);
});
