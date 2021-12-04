<?php

declare(strict_types=1);

use Toddy15\SpamDetect\SpamDetect;

it('can instantiate a spam detector', function () {
    $spamdetect = new SpamDetect();
    expect($spamdetect)->toBeInstanceOf(SpamDetect::class);
});
