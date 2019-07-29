<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

final class GenericObjectType extends ObjectType
{

	/** @var Type[] */
	private $types;

	/**
	 * @param Type[] $types
	 */
	public function __construct(
		string $mainType,
		array $types,
		?Type $subtractedType = null
	)
	{
		parent::__construct($mainType, $subtractedType);
		$this->types = $types;
	}

	public function describe(VerbosityLevel $level): string
	{
		return sprintf(
			'%s<%s>',
			parent::describe($level),
			implode(', ', array_map(static function (Type $type) use ($level): string {
				return $type->describe($level);
			}, $this->types))
		);
	}

}
