<?php

declare(strict_types=1);

\spl_autoload_register(function ($class)
{
    $unprefixedClass = \str_replace('Example\\', '', $class);

    if (\mb_strpos($unprefixedClass, 'Tests\\') === 0)
    {
        $unprefixedClass = \str_replace('Tests\\', '', $unprefixedClass);
        $path = __DIR__ . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR
            . 'tests' . DIRECTORY_SEPARATOR
            . $unprefixedClass . '.php';
    }
    else
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR
            . $unprefixedClass . '.php';
    }

    if (!\file_exists($path))
    {
        // Defer to other autoloaders
        return;
    }

    require_once $path;
});
