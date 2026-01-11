<?php

namespace Vortech\Stash\Facades;

use Illuminate\Support\Facades\Facade;

final class StashFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'stash';
    }
}
