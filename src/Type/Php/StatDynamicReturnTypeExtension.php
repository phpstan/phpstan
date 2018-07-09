<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

class StatDynamicReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension, \PHPStan\Type\DynamicMethodReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return in_array($functionReflection->getName(), ['stat', 'lstat', 'fstat', 'ssh2_sftp_stat'], true);
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		return $this->getReturnType();
	}

	public function getClass(): string
	{
		return \SplFileObject::class;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'fstat';
	}

	public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
	{
		return $this->getReturnType();
	}

	private function getReturnType(): Type
	{
		$valueType = new IntegerType();

		$builder = ConstantArrayTypeBuilder::createEmpty();

		$builder->setOffsetValueType(null, $valueType);
		$builder->setOffsetValueType(null, $valueType);
		$builder->setOffsetValueType(null, $valueType);
		$builder->setOffsetValueType(null, $valueType);
		$builder->setOffsetValueType(null, $valueType);
		$builder->setOffsetValueType(null, $valueType);
		$builder->setOffsetValueType(null, $valueType);
		$builder->setOffsetValueType(null, $valueType);
		$builder->setOffsetValueType(null, $valueType);
		$builder->setOffsetValueType(null, $valueType);
		$builder->setOffsetValueType(null, $valueType);
		$builder->setOffsetValueType(null, $valueType);
		$builder->setOffsetValueType(null, $valueType);

		$builder->setOffsetValueType(new ConstantStringType('dev'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('ino'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('mode'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('nlink'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('uid'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('gid'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('rdev'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('size'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('atime'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('mtime'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('ctime'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('blksize'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('blocks'), $valueType);

		return TypeCombinator::union($builder->getArray(), new ConstantBooleanType(false));
	}

}
