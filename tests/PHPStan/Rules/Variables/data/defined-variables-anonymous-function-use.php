<?php

$foo = 1;

function () use ($foo, $bar) {

};

function () use (&$errorHandler) {

};

$wrongErrorHandler = function () use ($wrongErrorHandler) {

};
