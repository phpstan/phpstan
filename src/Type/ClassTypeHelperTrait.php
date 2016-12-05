<?php declare(strict_types = 1);

namespace PHPStan\Type;

trait ClassTypeHelperTrait
{

	private function exists(string $className): bool
	{
		try {
			return class_exists($className) || interface_exists($className) || trait_exists($className);
		} catch (\Throwable $t) {
			throw new \PHPStan\Broker\ClassAutoloadingException(
				$className,
				$t
			);
		}
	}

}
