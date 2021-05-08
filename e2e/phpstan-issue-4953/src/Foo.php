<?php

class Foo
{
    public static function getExceptionClass(Exception $exception): string
    {
        // Returning `get_class` directly does not generate an error
        // return get_class($exception);

        $class = get_class($exception);

        return $class;
    }
}