<?php

namespace BooneStudios\Surreal;

use Illuminate\Support\Facades\Facade;

/**
 * @see \BooneStudios\Surreal\Skeleton\SkeletonClass
 */
class SurrealFacade extends Facade
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
