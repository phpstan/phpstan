<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

use PHPStan\Type\Type;

class Is
{
	public static function type(Type $type, string $className): bool
	{
		static $mapping = [
			\PHPStan\Type\VoidType::class => VoidType::class,
			\PHPStan\Type\MixedType::class => MixedType::class,
			\PHPStan\Type\NullType::class => NullType::class,
			\PHPStan\Type\BooleanType::class => BooleanType::class,
			\PHPStan\Type\IntegerType::class => IntegerType::class,
			\PHPStan\Type\FloatType::class => FloatType::class,
			\PHPStan\Type\StringType::class => StringType::class,
			\PHPStan\Type\ArrayType::class => ArrayType::class,
			\PHPStan\Type\ResourceType::class => ResourceType::class,
			\PHPStan\Type\ObjectType::class => ObjectType::class,
			\PHPStan\Type\StaticType::class => StaticType::class,
			\PHPStan\Type\ThisType::class => ThisType::class,
			\PHPStan\Type\IterableType::class => IterableType::class,
			\PHPStan\Type\CallableType::class => CallableType::class,
			\PHPStan\Type\UnionType::class => UnionType::class,
		];

		return is_a($type, $className) || is_a($type, $mapping[$className]);
	}
}
