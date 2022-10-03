<?php

namespace Boonestudios\LaravelSurrealdb;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Boonestudios\LaravelSurrealdb\Skeleton\SkeletonClass
 */
class LaravelSurrealdbFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-surrealdb';
    }
}
