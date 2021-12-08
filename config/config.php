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
];
