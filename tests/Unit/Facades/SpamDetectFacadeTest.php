<?php

declare(strict_types=1);

use Toddy15\SpamDetect\Facades\SpamDetect;

it('can access a SpamDetect facade (ham)', function () {
    SpamDetect::shouldReceive('trainHam')
        ->with('Test');

    SpamDetect::trainHam('Test');
});

it('can access a SpamDetect facade (spam)', function () {
    SpamDetect::shouldReceive('trainSpam')
        ->with('Word');

    SpamDetect::trainSpam('Word');
});

it('can access a SpamDetect facade (text)', function () {
    SpamDetect::shouldReceive('trainText')
        ->with('ham', 'Foo');

    SpamDetect::trainText('ham', 'Foo');
});

it('can access a SpamDetect facade (classify)', function () {
    SpamDetect::shouldReceive('classify')
        ->with('Example text')
        ->andReturn(0.25);

    $result = SpamDetect::classify('Example text');
    expect($result)->toBe(0.25);
});

it('gets the correct classification using a SpamDetect facade', function () {
    SpamDetect::trainHam('text');

    $result = SpamDetect::classify('Example text');
    expect($result)->toBe(0.25);
});
