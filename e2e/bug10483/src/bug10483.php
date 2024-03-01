<?php

function doFoo(mixed $filter): void {
    var_dump(filter_var("no", FILTER_VALIDATE_REGEXP));
}
