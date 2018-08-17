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
		$keys = [
			'dev',
			'ino',
			'mode',
			'nlink',
			'uid',
			'gid',
			'rdev',
			'size',
			'atime',
			'mtime',
			'ctime',
			'blksize',
			'blocks',
		];

		foreach ($keys as $key) {
			$builder->setOffsetValueType(null, $valueType);
		}

		foreach ($keys as $key) {
			$builder->setOffsetValueType(new ConstantStringType($key), $valueType);
		}

		return TypeCombinator::union($builder->getArray(), new ConstantBooleanType(false));
	}

}
