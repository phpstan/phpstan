<?php

echo $argc;
function () {
    echo $argc;
};

function () {
    global $argv;
    var_dump($argv);
};
