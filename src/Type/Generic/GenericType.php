<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class GenericType extends ObjectType
{

	/** @var Type[] */
	private $types;

	/**
	 * @param string $mainType
	 * @param Type[] $types
	 */
	public function __construct(string $mainType, array $types)
	{
		parent::__construct($mainType);
		$this->types = $types;
	}

	public function describe(VerbosityLevel $level): string
	{
		return sprintf(
			'%s<%s>',
			parent::describe($level),
			implode(', ', array_map(function (Type $type) use ($level): string {
				return $type->describe($level);
			}, $this->types))
		);
	}

	// todo isSuperTypeOf(), accepts()

}
