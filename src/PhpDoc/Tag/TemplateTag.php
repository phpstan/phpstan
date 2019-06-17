<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc\Tag;

use PHPStan\Type\Type;

class TemplateTag
{

	/** @var string */
	private $name;

	/** @var \PHPStan\Type\Type */
	private $bound;

	public function __construct(string $name, Type $bound)
	{
		$this->name = $name;
		$this->bound = $bound;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getBound(): Type
	{
		return $this->bound;
	}

	/**
	 * @param mixed[] $properties
	 * @return self
	 */
	public static function __set_state(array $properties): self
	{
		return new self(
			$properties['name'],
			$properties['bound']
		);
	}

}
