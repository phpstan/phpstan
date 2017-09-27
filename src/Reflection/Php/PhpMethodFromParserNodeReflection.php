<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;

class PhpMethodFromParserNodeReflection extends PhpFunctionFromParserNodeReflection implements MethodReflection
{

	/** @var \PHPStan\Reflection\ClassReflection */
	private $declaringClass;

	public function __construct(
		ClassReflection $declaringClass,
		ClassMethod $classMethod,
		array $realParameterTypes,
		array $phpDocParameterTypes,
		bool $realReturnTypePresent,
		Type $realReturnType,
		Type $phpDocReturnType = null
	)
	{
		parent::__construct(
			$classMethod,
			$realParameterTypes,
			$phpDocParameterTypes,
			$realReturnTypePresent,
			$realReturnType,
			$phpDocReturnType
		);
		$this->declaringClass = $declaringClass;
	}

	public function getDeclaringClass(): ClassReflection
	{
		return $this->declaringClass;
	}

	public function getPrototype(): MethodReflection
	{
		return $this->declaringClass->getNativeMethod($this->getClassMethod()->name)->getPrototype();
	}

	private function getClassMethod(): ClassMethod
	{
		/** @var \PhpParser\Node\Stmt\ClassMethod $functionLike */
		$functionLike = $this->getFunctionLike();
		return $functionLike;
	}

	public function isStatic(): bool
	{
		return $this->getClassMethod()->isStatic();
	}

	public function isPrivate(): bool
	{
		return $this->getClassMethod()->isPrivate();
	}

	public function isPublic(): bool
	{
		return $this->getClassMethod()->isPublic();
	}

	public function getReturnType(): Type
	{
		if ($this->getName() === '__construct') {
			return new VoidType();
		}

		return parent::getReturnType();
	}

}
