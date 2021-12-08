<?php

declare(strict_types=1);

/**
 * Configuration options for the SpamDetect package
 */

return [
    /**
     * Define the threshold for deciding if a text is ham.
     * The value must be between 0 and 1.
     * It is used for the methods
     * SpamDetect::isHam() and SpamDetect::isSpam().
     * Any text that is rated equal or below the threshold
     * will be considered ham.
     */
    'threshold_for_ham' => '0.40',

    /**
     * Define the threshold for deciding if a text is spam.
     * The value must be between 0 and 1.
     * It is used for the methods
     * SpamDetect::isHam() and SpamDetect::isSpam().
     * Any text that is rated equal or below the threshold
     * will be considered spam.
     */
    'threshold_for_spam' => '0.60',

    /**
     * The assumed probability, based on our general background
     * information, that a word we don't have any other experience
     * of will first appear in a spam.
     *
     * A reasonable starting value is 0.50.
     */
    'assumed_spam_probability_of_unknown_words' => '0.50',

    /**
     * The strength we want to give to our background information.
     *
     * A reasonable starting value is 1.00.
     */
    'strength_of_background_information' => '1.00',
];
