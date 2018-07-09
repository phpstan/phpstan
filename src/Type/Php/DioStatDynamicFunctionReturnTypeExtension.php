<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

class DioStatDynamicFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'dio_stat';
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		$valueType = new IntegerType();

		$builder = ConstantArrayTypeBuilder::createEmpty();

		$builder->setOffsetValueType(new ConstantStringType('device'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('inode'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('mode'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('nlink'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('uid'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('gid'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('device_type'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('size'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('blocksize'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('blocks'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('atime'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('mtime'), $valueType);
		$builder->setOffsetValueType(new ConstantStringType('ctime'), $valueType);

		return TypeCombinator::addNull($builder->getArray());
	}

}
