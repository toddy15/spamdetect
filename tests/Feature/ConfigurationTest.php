<?php

declare(strict_types=1);

use Toddy15\SpamDetect\SpamDetect;

it('returns different scores based on assumed_spam_probability_of_unknown_words', function () {
    $spamdetect = new SpamDetect();

    $spamdetect->trainHam('both-01 ham-01 ham-02');
    $spamdetect->trainHam('ham-03 ham-04 ham-05');
    $spamdetect->trainSpam('both-01 spam-01 spam-02 spam-03');
    $spamdetect->trainSpam('spam-04 spam-05');
    $spamdetect->trainSpam('both-01 spam-06 spam-07 spam-08');

    $input_texts = [
        // this is probably ham
        'unknown-01 unknown-02 unknown-03 ham-01 ham-02 ham-03',
        // this is probably spam
        'unknown-01 unknown-02 spam-01',
        // this is probably ham
        'unknown-01 unknown-02 both-01 ham-01 spam-01',
    ];

    // Normal probability of spam, using default values
    $expected_scores = [0.0357, 0.75, 0.425];
    $input = array_combine($input_texts, $expected_scores);
    foreach ($input as $input_text => $score) {
        expect($spamdetect->classify($input_text))->toBe($score);
    }

    // Higher probability of spam
    config(['spamdetect.assumed_spam_probability_of_unknown_words' => 0.75]);

    $expected_scores = [0.1776, 0.875, 0.7998];
    $input = array_combine($input_texts, $expected_scores);
    foreach ($input as $input_text => $score) {
        expect($spamdetect->classify($input_text))->toBe($score);
    }

    // Lower probability of spam
    config(['spamdetect.assumed_spam_probability_of_unknown_words' => 0.25]);

    $expected_scores = [0.0029, 0.625, 0.1192];
    $input = array_combine($input_texts, $expected_scores);
    foreach ($input as $input_text => $score) {
        expect($spamdetect->classify($input_text))->toBe($score);
    }

    // Reset to normal probability of spam, using default values
    config(['spamdetect.assumed_spam_probability_of_unknown_words' => 0.5]);

    $expected_scores = [0.0357, 0.75, 0.425];
    $input = array_combine($input_texts, $expected_scores);
    foreach ($input as $input_text => $score) {
        expect($spamdetect->classify($input_text))->toBe($score);
    }
});

it('returns different scores based on strength_of_background_information', function () {
    $spamdetect = new SpamDetect();

    $spamdetect->trainHam('both-01 ham-01 ham-02');
    $spamdetect->trainHam('ham-03 ham-04 ham-05');
    $spamdetect->trainSpam('both-01 spam-01 spam-02 spam-03');
    $spamdetect->trainSpam('spam-04 spam-05');
    $spamdetect->trainSpam('both-01 spam-06 spam-07 spam-08');

    $input_texts = [
        // this is probably ham
        'unknown-01 unknown-02 unknown-03 ham-01 ham-02 ham-03',
        // this is probably spam
        'unknown-01 unknown-02 spam-01',
        // this is probably ham
        'unknown-01 unknown-02 both-01 ham-01 spam-01',
    ];

    // Normal probability of spam, using default values
    $expected_scores = [0.0357, 0.75, 0.425];
    $input = array_combine($input_texts, $expected_scores);
    foreach ($input as $input_text => $score) {
        expect($spamdetect->classify($input_text))->toBe($score);
    }

    // Higher strength of background information
    config(['spamdetect.strength_of_background_information' => 3.2]);

    $expected_scores = [0.189, 0.619, 0.4516];
    $input = array_combine($input_texts, $expected_scores);
    foreach ($input as $input_text => $score) {
        expect($spamdetect->classify($input_text))->toBe($score);
    }

    // Lower strength of background information
    config(['spamdetect.strength_of_background_information' => 0.2]);

    $expected_scores = [0.0008, 0.9167, 0.4063];
    $input = array_combine($input_texts, $expected_scores);
    foreach ($input as $input_text => $score) {
        expect($spamdetect->classify($input_text))->toBe($score);
    }

    // Disabled background information
    config(['spamdetect.strength_of_background_information' => 0]);

    $expected_scores = [0.0, 0.99, 0.4];
    $input = array_combine($input_texts, $expected_scores);
    foreach ($input as $input_text => $score) {
        expect($spamdetect->classify($input_text))->toBe($score);
    }
});
