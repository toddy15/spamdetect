<?php

declare(strict_types=1);

namespace Toddy15\SpamDetect;

class SpamDetect
{
    /**
     * Classify a text.
     *
     * The function return a float value between 0.0 and 1.0,
     * representing the likelihood of the text being ham or spam.
     * Lower values towards 0.0 indicate 'ham', higher values
     * towards 1.0 indicate 'spam'.
     */
    public function classify(string $string): float
    {
        return 0.5;
    }
}
