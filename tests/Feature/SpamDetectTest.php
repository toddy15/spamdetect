<?php

declare(strict_types=1);

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
