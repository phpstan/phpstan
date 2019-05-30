<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

class TemplateTypeMap
{

	/** @var array<string,\PHPStan\Type\Type> */
	private $types;

	/** @param array<string,\PHPStan\Type\Type> $types */
	public function __construct(array $types)
	{
		$this->types = $types;
	}

	public static function empty(): self
	{
		return new self([]);
	}

	/** @return array<string,\PHPStan\Type\Type> */
	public function getTypes(): array
	{
		return $this->types;
	}

	public function getType(string $name): ?Type
	{
		return $this->types[$name] ?? null;
	}

	public function union(self $other): self
	{
		$result = $this->types;

		foreach ($other->types as $name => $type) {
			if (isset($result[$name])) {
				$result[$name] = TypeCombinator::union($result[$name], $type);
			} else {
				$result[$name] = $type;
			}
		}

		return new self($result);
	}

	public function intersect(self $other): self
	{
		$result = $this->types;

		foreach ($other->types as $name => $type) {
			if (isset($result[$name])) {
				$result[$name] = TypeCombinator::intersect($result[$name], $type);
			} else {
				$result[$name] = $type;
			}
		}

		return new self($result);
	}

}
