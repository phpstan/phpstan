<?php

namespace FunctionWithNullableVariadicParameters;

function () {
	foo();

	foo(1, 2);

	foo(1, 2, 3);

	foo(1, 2, null);

};
