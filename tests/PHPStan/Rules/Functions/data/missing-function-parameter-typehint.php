<?php

namespace
{
	/**
	 * @param int $a
	 * @param $b
	 */
	function globalFunction($a, $b, $c): bool
	{
		$closure = function($a, $b, $c) {

		};

		return false;
	}
}

namespace MissingFunctionParameterTypehint
{
	/**
	 * @param $d
	 */
	function namespacedFunction($d, bool $e): int {
		return 9;
	};
}
