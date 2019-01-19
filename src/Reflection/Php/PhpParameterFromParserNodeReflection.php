<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;

class PhpParameterFromParserNodeReflection implements \PHPStan\Reflection\ParameterReflectionWithPhpDocs
{

	/** @var string */
	private $name;

	/** @var bool */
	private $optional;

	/** @var \PHPStan\Type\Type */
	private $realType;

	/** @var \PHPStan\Type\Type|null */
	private $phpDocType;

	/** @var \PHPStan\Reflection\PassedByReference */
	private $passedByReference;

	/** @var \PhpParser\Node\Expr|null */
	private $defaultValue;

	/** @var bool */
	private $variadic;

	/** @var \PHPStan\Type\Type|null */
	private $type;

	public function __construct(
		string $name,
		bool $optional,
		Type $realType,
		?Type $phpDocType,
		PassedByReference $passedByReference,
		?Expr $defaultValue,
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

	public function getPhpDocType(): Type
	{
		return $this->phpDocType ?? new MixedType();
	}

	public function getNativeType(): Type
	{
		return $this->realType ?? new MixedType();
	}

	public function passedByReference(): PassedByReference
	{
		return $this->passedByReference;
	}

	public function isVariadic(): bool
	{
		return $this->variadic;
	}

}
