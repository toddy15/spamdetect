<?php

declare(strict_types=1);

namespace Toddy15\SpamDetect\Facades;

use Illuminate\Support\Facades\Facade;

class SpamDetect extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'spamdetect';
    }
}
