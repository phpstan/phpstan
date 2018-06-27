<?php

namespace CallToFunctionInForeachCondition;

function takesInt(int $i): void
{
}

function takesString(string $i): void
{
}

function () {
    $a = null;
    foreach ([1, 2, 3] as $i) {
        if ($a) {
            takesInt($a);
            takesString($a);
        }
        $a = $i;
    }
};
