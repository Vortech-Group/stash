<?php

declare(strict_types=1);

use Vortech\Stash\Facades\StashFacade;

if (! function_exists('stash')) {
    function stash(string $fileName = 'default', array|null $values = null) {
        return StashFacade::getFacadeRoot()->make($fileName, $values);
    }
}