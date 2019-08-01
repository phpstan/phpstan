<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassReflection;
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

	public function getClassReflection(): ?ClassReflection
	{
		$broker = Broker::getInstance();
		if (!$broker->hasClass($this->getClassName())) {
			return null;
		}

		return $broker->getClass($this->getClassName())->withTypes($this->types);
	}

	public function traverse(callable $cb): Type
	{
		$subtractedType = $this->getSubtractedType() !== null ? $cb($this->getSubtractedType()) : null;

		$typesChanged = false;
		$types = [];
		foreach ($this->types as $type) {
			$newType = $cb($type);
			if ($newType === $type) {
				continue;
			}

			$typesChanged = true;
			$types[] = $newType;
		}

		if ($subtractedType !== $this->getSubtractedType() || $typesChanged) {
			return new static(
				$this->getClassName(),
				$types,
				$subtractedType
			);
		}

		return $this;
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self(
			$properties['className'],
			$properties['types'],
			$properties['subtractedType'] ?? null
		);
	}

}
