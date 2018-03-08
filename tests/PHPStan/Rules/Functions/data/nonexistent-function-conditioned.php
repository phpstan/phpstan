<?php

if (function_exists('foobarNonExistentFunction')) {
	foobarNonExistentFunction();
}

function_exists('foobarNonExistentFunction') && foobarNonExistentFunction();
