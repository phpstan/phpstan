<?php

namespace FunctionWithVariadicParameters;

function () {
    foo();

    foo(1, 2);

    foo(1, 2, 3);

    foo(1, 2, null);

    bar();

    bar(1, 2);
};
