<?php

declare(strict_types=1);

namespace Toddy15\SpamDetect;

class Tokenizer
{
    /**
     * Define start and ending tags of some common markups:
     * - HTML or XML tags like <h1>
     * - Emoji notation like :smile:
     * - Emoji notation like [wink]
     *
     * Each array consists of the start and end delimiter
     * for the markup.
     */
    private array $commonMarkups = [
        ['<', '>'],
        [':', ':'],
        // Needs to be escaped for the regular expression.
        ['\[', '\]'],
    ];

    /**
     * Current result of tokenizing
     */
    private array $tokens;

    public function __construct(array $input)
    {
        $this->tokens = $input;
    }

    /**
     * Split input into tokens.
     */
    public function tokenize(): array
    {
        foreach ($this->commonMarkups as $delimiters) {
            $start = $delimiters[0];
            $end = $delimiters[1];
            $this->tokenizeCommonMarkups($start, $end);
        }

        // Perform the splitting on whitespace
        $result = [];
        foreach ($this->tokens as $token) {
            $split_tokens = preg_split("/\s+/", $token);
            if ($split_tokens === false) {
                $split_tokens = [$token];
            }
            $result = array_merge($result, array_filter($split_tokens));
        }

        // Finally, remove identical tokens. Each token
        // should appear only once in the result.
        return array_values(array_unique($result));
    }

    /**
     * Split common markups into tokens.
     */
    private function tokenizeCommonMarkups(string $start, string $end): void
    {
        $pattern = '/(' . $start . '[^' . $end . '\s]+?' . $end . ')/';
        $result = [];

        foreach ($this->tokens as $s) {
            $tokens = preg_split($pattern, $s, 0, PREG_SPLIT_DELIM_CAPTURE);
            if ($tokens === false) {
                $tokens = [$s];
            }
            foreach ($tokens as $token) {
                $token = trim($token);
                if ($token == '') {
                    continue;
                }
                $result[] = $token;
            }
        }

        $this->tokens = $result;
    }
}
