<?php

declare(strict_types=1);

namespace Toddy15\SpamDetect;

use Toddy15\SpamDetect\Models\Token;

class SpamDetect
{
    /**
     * The special token, holding stats about ham and spam texts.
     */
    private Token $stats;

    /**
     * Initialize the class with stats about ham and spam texts.
     */
    public function __construct()
    {
        $this->stats = Token::find(1);
    }

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
        $this->stats->refresh();

        // If there are no texts yet, the probability is 0.5
        if ($this->stats->count_ham === 0 and $this->stats->count_spam === 0) {
            return 0.5;
        }

        $tokenizer = new Tokenizer([$string]);
        $found_tokens = $tokenizer->tokenize();
        $probabilities = $this->getTokenProbabilities($found_tokens);
        $importantTokens = $this->getImportantTokens($probabilities);

        // In order to avoid floating point underflow,
        // do the calculation in the logarithmic domain.
        $result = 0;
        foreach ($importantTokens as $probability) {
            $result += log(1 - $probability) - log($probability);
        }
        return round(1 / (1 + exp($result)), 4);
    }

    /**
     * Helper function to get individual probabilities of tokens
     */
    private function getTokenProbabilities(array $found_tokens): array
    {
        // If there are only ham *or* spam texts in the database,
        // ensure that there is no count of zero. Otherwise
        // the calculation below will divide by zero.
        $count_ham_texts = max($this->stats->count_ham, 1);
        $total_spam_texts = max($this->stats->count_spam, 1);

        $probabilities = [];
        foreach ($found_tokens as $found_token) {
            $token = Token::firstWhere(['token' => $found_token]);
            if (is_null($token)) {
                $probabilities[$found_token] = 0.5;
                continue;
            }
            $relative_frequency_bad = min($token->count_spam / $total_spam_texts, 1);
            $relative_frequency_good = min(2 * $token->count_ham / $count_ham_texts, 1);
            $probability = round($relative_frequency_bad / ($relative_frequency_good + $relative_frequency_bad), 14);
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
        // Use at most 15 tokens.
        $tokens = array_keys(array_slice($importance, 0, 15));
        $importantTokens = [];
        foreach ($tokens as $token){
            $importantTokens[$token] = $probabilities[$token];
        }
        return $importantTokens;
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
        $this->stats->refresh();
        $this->stats->count_ham++;
        $this->stats->save();
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
        $this->stats->refresh();
        $this->stats->count_spam++;
        $this->stats->save();
    }
}
