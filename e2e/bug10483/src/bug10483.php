<?php

function doFoo(mixed $filter): void {
    $x = filter_input_array(INPUT_GET, [
        'int' => FILTER_SANITIZE_MAGIC_QUOTES,
        'positive_int' => $filter,
    ], true);

    var_dump($x);
}
