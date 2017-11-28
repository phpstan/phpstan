<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc\Tag;

use PHPStan\Type\Type;

class ReturnTag
{

	/** @var \PHPStan\Type\Type */
	private $type;

	public function __construct(Type $type)
	{
		$this->type = $type;
	}

	public function getType(): Type
	{
		return $this->type;
	}

	public static function __set_state(array $properties): self
	{
		return new self(
			$properties['type']
		);
	}

}
