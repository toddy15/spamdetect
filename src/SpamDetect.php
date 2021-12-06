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
        $stats = Token::find(1);

        // If there are no texts yet, the probability is 0.5
        if ($stats->count_ham === 0 and $stats->count_spam === 0) {
            return 0.5;
        }

        $tokenizer = new Tokenizer([$string]);
        $found_tokens = $tokenizer->tokenize();
        $probabilities = $this->getTokenProbabilities($stats, $found_tokens);
        $importantTokens = $this->getImportantTokens($probabilities);

        return 0.5;
    }

    /**
     * Helper function to get individual probabilities of tokens
     */
    private function getTokenProbabilities(Token $stats, $found_tokens): array
    {
        // If there are only ham *or* spam texts in the database,
        // ensure that there is no count of zero. Otherwise
        // the calculation below will divide by zero.
        $count_ham_texts = max($stats->count_ham, 1);
        $total_spam_texts = max($stats->count_spam, 1);

        $probabilities = [];
        foreach ($found_tokens as $found_token) {
            $token = Token::firstWhere(['token' => $found_token]);
            if (is_null($token)) {
                $probabilities[$found_token] = 0.5;
                continue;
            }
            $relative_frequency_bad = min($token->count_spam / $total_spam_texts, 1);
            $relative_frequency_good = min(2 * $token->count_ham / $count_ham_texts, 1);
            $probability = $relative_frequency_bad / ($relative_frequency_good + $relative_frequency_bad);
            // Ensure a probability between 0.01 and 0.99
            $probabilities[$found_token] = max(min($probability, 0.99), 0.01);
        }
        return $probabilities;
    }

    private function getImportantTokens(array $probabilities): array
    {
        $importance = [];
        // The "importance" is used to extract the most meaningful
        // tokens, i.e. those which are near 0 or 1.
        foreach ($probabilities as $token => $probability) {
            $importance[$token] = abs(0.5 - $probability);
        }
        // Sort importance from the highest value to lowest,
        // maintaining the key (= token).
        arsort($importance);
        // Return at most 15 tokens.
        return array_keys(array_slice($importance, 0, 15));
    }

    /**
     * Split the given string into tokens and add them to the ham database.
     */
    public function trainHam(string $string): void
    {
        $tokenizer = new Tokenizer([$string]);
        foreach ($tokenizer->tokenize() as $token) {
            $existing_token = Token::firstOrNew([
                'token' => $token,
            ]);
            $existing_token->count_ham++;
            $existing_token->save();
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
            $existing_token = Token::firstOrNew([
                'token' => $token,
            ]);
            $existing_token->count_spam++;
            $existing_token->save();
        }
        $stats = Token::find(1);
        $stats->count_spam++;
        $stats->save();
    }
}
