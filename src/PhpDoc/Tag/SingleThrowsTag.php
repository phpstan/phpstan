<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc\Tag;

use PHPStan\Type\Type;

class SingleThrowsTag
{

	/** @var \PHPStan\Type\Type */
	private $type;

	/** @var string */
	private $description;

	public function __construct(Type $type, string $description)
	{
		$this->type = $type;
		$this->description = $description;
	}

	public function getType(): Type
	{
		return $this->type;
	}

	public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * @param mixed[] $properties
	 * @return SingleThrowsTag
	 */
	public static function __set_state(array $properties): self
	{
		return new self(
			$properties['type'],
			$properties['description']
		);
	}

}
