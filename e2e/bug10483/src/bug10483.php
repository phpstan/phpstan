<?php

function doFoo(mixed $filter): void {
    $x = filter_input_array(INPUT_GET, [
        'int' => FILTER_VALIDATE_BOOL
    ], true);
}
