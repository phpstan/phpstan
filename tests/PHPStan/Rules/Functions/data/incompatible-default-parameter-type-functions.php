<?php

namespace IncompatibleDefaultParameter;

/**
 * @param int $int
 */
function takesInt($int = 10): void
{
}

/**
 * @param string $string
 */
function takesString($string = false): void
{
}
