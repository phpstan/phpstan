<?php

namespace ReusingSpecifiedVariableInForeach;

/** @var string|null $business */
$business = doFoo();
if ($business !== null) {
    return;
}

foreach ([1, 2, 3] as $business) {
    die;
}
