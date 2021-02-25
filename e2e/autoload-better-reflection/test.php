<?php

namespace TestBetterReflectionAutoload;

use PHPStan\BetterReflection\Identifier\Identifier;
use PHPStan\BetterReflection\Identifier\IdentifierType;
use PHPStan\BetterReflection\Reflection\Reflection;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Type\SourceLocator;

require_once __DIR__ . '/../../bootstrap.php';

class Foo implements SourceLocator
{

	public function locateIdentifier(
		Reflector $reflector,
		Identifier $identifier
	): ?Reflection
	{
		return null;
	}

	public function locateIdentifiersByType(Reflector $reflector, IdentifierType $identifierType): array
	{
		return [];
	}

}
