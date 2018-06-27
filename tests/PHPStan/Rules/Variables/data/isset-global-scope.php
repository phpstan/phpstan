<?php

$alwaysDefinedNotNullable = 'string';
if (doFoo()) {
    $sometimesDefinedVariable = 1;
}

if (isset(
    $alwaysDefinedNotNullable, // always true
    $sometimesDefinedVariable, // fine, this is what's isset() is for
    $neverDefinedVariable // always false - do not report in global scope
)) {
}
