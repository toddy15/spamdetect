<?php

declare(strict_types=1);

namespace Toddy15\SpamDetect;

class Tokenizer
{
    /**
     * Define start and ending tag of some common markups:
     * - HTML or XML tags like <h1>
     * - Emoji notation like :smile:
     */
    private array $commonMarkups = [
        ['<', '>'],
        [':', ':'],
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
        $result = [];
        foreach ($this->tokens as $token) {
            $result = array_merge($result, array_filter(preg_split("/\s+/", $token)));
        }

        $this->tokens = $result;

        return $this->tokens;
    }

    /**
     * Split common markups into tokens.
     */
    private function tokenizeCommonMarkups(string $start, string $end): void
    {
        $pattern = '/(' . $start.'[^'.$end.'\s]+?'.$end.')/';
        $result = [];

        foreach ($this->tokens as $s) {
            $tokens = preg_split($pattern, $s, 0, PREG_SPLIT_DELIM_CAPTURE);
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
