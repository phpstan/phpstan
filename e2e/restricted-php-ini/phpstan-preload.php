<?php

set_error_handler(
    function (int $severity, string $msg, string $file, int $line): bool {
        if ((error_reporting() & ~4437) === 0) {
            return false;
        }

        throw new \ErrorException($msg, 0, $severity, $file, $line);
    },
    \E_ALL
);

eval('class XB {}');
