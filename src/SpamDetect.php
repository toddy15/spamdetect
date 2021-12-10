<?php

declare(strict_types=1);

namespace Toddy15\SpamDetect;

use Toddy15\SpamDetect\Models\Token;

class SpamDetect
{
    /**
     * The special token, holding stats about ham and spam texts.
     */
    private ?Token $stats;

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
     * The function returns a float value between 0.0 and 1.0,
     * representing the likelihood of the text being ham or spam.
     * Lower values towards 0.0 indicate 'ham', higher values
     * towards 1.0 indicate 'spam'.
     */
    public function classify(string $string): float
    {
        if (is_null($this->stats)) {
            return 0.5;
        }

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

        $score = round(1 / (1 + exp($result)), 4);

        return max(min($score, 1.0), 0.0);
    }

    /**
     * Helper function to get individual probabilities of tokens
     */
    private function getTokenProbabilities(array $found_tokens): array
    {
        $count_ham_texts = 1;
        $total_spam_texts = 1;
        if (! is_null($this->stats)) {
            // If there are only ham *or* spam texts in the database,
            // ensure that there is no count of zero. Otherwise
            // the calculation below will divide by zero.
            $count_ham_texts = max($this->stats->count_ham, 1);
            $total_spam_texts = max($this->stats->count_spam, 1);
        }

        $probabilities = [];
        foreach ($found_tokens as $found_token) {
            $token = Token::firstWhere(['token' => $found_token]);
            if (is_null($token)) {
                $probabilities[$found_token] = 0.5;

                continue;
            }

            $relative_frequency_spam = min($token->count_spam / $total_spam_texts, 1);
            $relative_frequency_ham = min(2 * $token->count_ham / $count_ham_texts, 1);
            $probability = $relative_frequency_spam / ($relative_frequency_ham + $relative_frequency_spam);

            // Calculate the better probability proposed by Gary Robinson.
            // This handles the case of rare words much better.
            // Reference: A Statistical Approach to the Spam Problem.
            // https://www.linuxjournal.com/article/6467
            //
            // - x is our assumed probability, based on our general background
            //     information, that a token we don't have any other experience
            //     of will first appear in a spam.
            // - s is the strength we want to give to our background information.
            // - n is the number of texts we have trained that contain the token.
            $x = (float)config('spamdetect.assumed_spam_probability_of_unknown_words', 0.5);
            $s = (float)config('spamdetect.strength_of_background_information', 1.0);
            $n = $token->count_ham + $token->count_spam;
            $probability = (($s * $x) + ($n * $probability)) / ($s + $n);

            // Ensure a probability between 0.01 and 0.99
            // and a precision of 14 decimal digits
            $probability = max(min($probability, 0.99), 0.01);
            $probabilities[$found_token] = round($probability, 14);
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
        foreach ($tokens as $token) {
            $importantTokens[$token] = $probabilities[$token];
        }

        return $importantTokens;
    }

    /**
     * Split the given string into tokens and add them to the ham database.
     */
    public function trainHam(string $string): void
    {
        $this->trainText($string, 'ham');
    }

    /**
     * Split the given string into tokens and add them to the spam database.
     */
    public function trainText(string $string, string $category): void
    {
        if (is_null($this->stats)) {
            return;
        }

        if ($category === 'ham') {
            $ham = 1;
            $spam = 0;
        } elseif ($category === 'spam') {
            $ham = 0;
            $spam = 1;
        } else {
            return;
        }

        $tokenizer = new Tokenizer([$string]);
        foreach ($tokenizer->tokenize() as $token) {
            $existing_token = Token::firstOrNew([
                'token' => $token,
            ]);
            $existing_token->count_ham += $ham;
            $existing_token->count_spam += $spam;
            $existing_token->save();
        }
        $this->stats->refresh();
        $this->stats->count_ham += $ham;
        $this->stats->count_spam += $spam;
        $this->stats->save();
    }

    /**
     * Split the given string into tokens and add them to the spam database.
     */
    public function trainSpam(string $string): void
    {
        $this->trainText($string, 'spam');
    }
}
