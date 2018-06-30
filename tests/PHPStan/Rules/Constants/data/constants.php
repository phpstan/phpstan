<?php

namespace Constants;

use const OtherConstants\BAZ_CONSTANT;

echo FOO_CONSTANT;
echo BAR_CONSTANT;
echo BAZ_CONSTANT;
echo NONEXISTENT_CONSTANT;

function () {
	echo DEFINED_CONSTANT;
	define('DEFINED_CONSTANT', true);
	echo DEFINED_CONSTANT;

	if (defined('DEFINED_CONSTANT_IF')) {
		echo DEFINED_CONSTANT_IF;
	}

	echo DEFINED_CONSTANT_IF;

	if (!defined("OMIT_INDIC_FIX_1") || OMIT_INDIC_FIX_1 != 1) {
		// ...
	}
};
