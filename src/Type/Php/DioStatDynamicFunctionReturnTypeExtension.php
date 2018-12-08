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
		$keys = [
			'device',
			'inode',
			'mode',
			'nlink',
			'uid',
			'gid',
			'device_type',
			'size',
			'blocksize',
			'blocks',
			'atime',
			'mtime',
			'ctime',
		];

		foreach ($keys as $key) {
			$builder->setOffsetValueType(new ConstantStringType($key), $valueType);
		}

		return TypeCombinator::addNull($builder->getArray());
	}

}
