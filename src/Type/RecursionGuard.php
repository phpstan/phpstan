<?php declare(strict_types = 1);

namespace PHPStan\Type;

class RecursionGuard
{

	/** @var true[] */
	private static $context = [];

	public static function run(Type $type, callable $callback): Type
	{
		$key = $type->describe(VerbosityLevel::value());
		if (isset(self::$context[$key])) {
			return new ErrorType();
		}

		try {
			self::$context[$key] = true;
			return $callback();
		} finally {
			unset(self::$context[$key]);
		}
	}

}
