<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

// Uses the given test case and trait in the current folder recursively
uses(RefreshDatabase::class)->in(__DIR__);
