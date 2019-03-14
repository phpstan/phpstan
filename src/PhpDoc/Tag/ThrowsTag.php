<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc\Tag;

use PHPStan\Type\Type;

class ThrowsTag
{

	/** @var \PHPStan\Type\Type */
	private $type;

	/** @var array<SingleThrowsTag> */
	private $throwsTags;

	/**
	 * @param Type $type
	 * @param array<SingleThrowsTag> $throwsTags
	 */
	public function __construct(Type $type, array $throwsTags)
	{
		$this->type = $type;
		$this->throwsTags = $throwsTags;
	}

	public function getType(): Type
	{
		return $this->type;
	}

	/**
	 * @return array<SingleThrowsTag>
	 */
	public function getThrowsTags(): array
	{
		return $this->throwsTags;
	}

	/**
	 * @param mixed[] $properties
	 * @return ThrowsTag
	 */
	public static function __set_state(array $properties): self
	{
		return new self(
			$properties['type'],
			$properties['throwsTags']
		);
	}

}
