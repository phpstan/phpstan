<?php

function doFoo(mixed $filter): void {
    $x = filter_input_array(INPUT_GET, [
        'int' => FILTER_SANITIZE_ADD_SLASHES
    ], true);
}
