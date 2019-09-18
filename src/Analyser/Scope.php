<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

interface Scope extends ClassMemberAccessAnswerer
{

	public function getFile(): string;

	public function getFileDescription(): string;

	public function isDeclareStrictTypes(): bool;

	public function isInTrait(): bool;

	public function getTraitReflection(): ?ClassReflection;

	/**
	 * @return \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection|null
	 */
	public function getFunction();

	public function getFunctionName(): ?string;

	public function getNamespace(): ?string;

	public function hasVariableType(string $variableName): TrinaryLogic;

	public function getVariableType(string $variableName): Type;

	public function hasConstant(Name $name): bool;

	public function isInAnonymousFunction(): bool;

	public function getAnonymousFunctionReflection(): ?ParametersAcceptor;

	public function getAnonymousFunctionReturnType(): ?\PHPStan\Type\Type;

	public function getType(Expr $node): Type;

	public function resolveName(Name $name): string;

	/**
	 * @param mixed $value
	 */
	public function getTypeFromValue($value): Type;

	public function isSpecified(Expr $node): bool;

	public function isInClosureBind(): bool;

	public function isParameterValueNullable(Param $parameter): bool;

	/**
	 * @param \PhpParser\Node\Name|\PhpParser\Node\Identifier|\PhpParser\Node\NullableType|null $type
	 * @param bool $isNullable
	 * @param bool $isVariadic
	 * @return Type
	 */
	public function getFunctionType($type, bool $isNullable, bool $isVariadic): Type;

	public function isInExpressionAssign(Expr $expr): bool;

	public function filterByTruthyValue(Expr $expr, bool $defaultHandleFunctions = false): self;

	public function filterByFalseyValue(Expr $expr, bool $defaultHandleFunctions = false): self;

	public function isInFirstLevelStatement(): bool;

}
