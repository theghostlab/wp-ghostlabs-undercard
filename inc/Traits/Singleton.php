<?php

namespace GHOSTLABS\UNDERCARD\Traits;

trait Singleton
{
    
    private static array $instances = [];

    final public function __construct() {}
    final public function __clone() {}
    final public function __wakeup() {}

    final public static function getInstance(): static
    {
        return self::$instances[static::class] ??= new static();
    }
}
