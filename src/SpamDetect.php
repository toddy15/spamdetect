<?php

declare(strict_types=1);

namespace Toddy15\SpamDetect;

use Toddy15\SpamDetect\Models\Token;

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

    /**
     * Split the given string into tokens and add them to the ham database.
     */
    public function trainHam(string $string): void
    {
        $tokenizer = new Tokenizer([$string]);
        foreach ($tokenizer->tokenize() as $token) {
            Token::create([
                'token' => $token,
                'count_ham' => 1,
            ]);
        }
        $stats = Token::find(1);
        $stats->count_ham++;
        $stats->save();
    }

    /**
     * Split the given string into tokens and add them to the spam database.
     */
    public function trainSpam(string $string)
    {
        $tokenizer = new Tokenizer([$string]);
        foreach ($tokenizer->tokenize() as $token) {
            Token::create([
                'token' => $token,
                'count_spam' => 1,
            ]);
        }
        $stats = Token::find(1);
        $stats->count_spam++;
        $stats->save();
    }
}
