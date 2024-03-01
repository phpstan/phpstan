<?php

function doFoo($filter) {
    $x = filter_input_array(INPUT_GET, [
        'int' => FILTER_VALIDATE_INT,
        'positive_int' => $filter,
    ], true);

    var_dump($x);
}
