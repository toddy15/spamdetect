<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Toddy15\SpamDetect\Tests\TestCase;

// Uses the given test case and trait in the current folder recursively
uses(LazilyRefreshDatabase::class)->in(__DIR__);

uses(TestCase::class)->in(__DIR__);
