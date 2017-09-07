<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;

class PhpParameterFromParserNodeReflection implements \PHPStan\Reflection\ParameterReflection
{

	/** @var string */
	private $name;

	/** @var bool */
	private $optional;

	/** @var \PHPStan\Type\Type */
	private $realType;

	/** @var \PHPStan\Type\Type|null */
	private $phpDocType;

	/** @var bool */
	private $passedByReference;

	/** @var \PhpParser\Node\Expr|null */
	private $defaultValue;

	/** @var bool */
	private $variadic;

	/** @var \PHPStan\Type\Type */
	private $type;

	public function __construct(
		string $name,
		bool $optional,
		Type $realType,
		Type $phpDocType = null,
		bool $passedByReference,
		Expr $defaultValue = null,
		bool $variadic
	)
	{
		$this->name = $name;
		$this->optional = $optional;
		$this->realType = $realType;
		$this->phpDocType = $phpDocType;
		$this->passedByReference = $passedByReference;
		$this->defaultValue = $defaultValue;
		$this->variadic = $variadic;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function isOptional(): bool
	{
		return $this->optional;
	}

	public function getType(): Type
	{
		if ($this->type === null) {
			$phpDocType = $this->phpDocType;
			if ($phpDocType !== null && $this->defaultValue !== null) {
				if (
					$this->defaultValue instanceof ConstFetch
					&& strtolower((string) $this->defaultValue->name) === 'null'
				) {
					$phpDocType = \PHPStan\Type\TypeCombinator::addNull($phpDocType);
				}
			}
			$this->type = TypehintHelper::decideType($this->realType, $phpDocType);
		}

		return $this->type;
	}

	public function isPassedByReference(): bool
	{
		return $this->passedByReference;
	}

	public function isVariadic(): bool
	{
		return $this->variadic;
	}

}
