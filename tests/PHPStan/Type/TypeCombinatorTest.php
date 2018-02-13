<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;

class TypeCombinatorTest extends \PHPStan\Testing\TestCase
{

	protected function setUp(): void
	{
		parent::setUp();
		$this->createBroker();
	}

	public function dataAddNull(): array
	{
		return [
			[
				new MixedType(),
				MixedType::class,
				'mixed',
			],
			[
				new NullType(),
				NullType::class,
				'null',
			],
			[
				new VoidType(),
				UnionType::class,
				'void|null',
			],
			[
				new StringType(),
				UnionType::class,
				'string|null',
			],
			[
				new UnionType([
					new StringType(),
					new IntegerType(),
				]),
				UnionType::class,
				'int|string|null',
			],
			[
				new UnionType([
					new StringType(),
					new IntegerType(),
					new NullType(),
				]),
				UnionType::class,
				'int|string|null',
			],
			[
				new IntersectionType([
					new IterableType(new MixedType(), new StringType()),
					new ObjectType('ArrayObject'),
				]),
				UnionType::class,
				'(ArrayObject&iterable<string>)|null',
			],
			[
				new UnionType([
					new IntersectionType([
						new IterableType(new MixedType(), new StringType()),
						new ObjectType('ArrayObject'),
					]),
					new NullType(),
				]),
				UnionType::class,
				'(ArrayObject&iterable<string>)|null',
			],
		];
	}

	/**
	 * @dataProvider dataAddNull
	 * @param \PHPStan\Type\Type $type
	 * @param string $expectedTypeClass
	 * @param string $expectedTypeDescription
	 */
	public function testAddNull(
		Type $type,
		string $expectedTypeClass,
		string $expectedTypeDescription
	): void
	{
		$result = TypeCombinator::addNull($type);
		$this->assertSame($expectedTypeDescription, $result->describe());
		$this->assertInstanceOf($expectedTypeClass, $result);
	}

	/**
	 * @dataProvider dataAddNull
	 * @param \PHPStan\Type\Type $type
	 * @param string $expectedTypeClass
	 * @param string $expectedTypeDescription
	 */
	public function testUnionWithNull(
		Type $type,
		string $expectedTypeClass,
		string $expectedTypeDescription
	): void
	{
		$result = TypeCombinator::union($type, new NullType());
		$this->assertSame($expectedTypeDescription, $result->describe());
		$this->assertInstanceOf($expectedTypeClass, $result);
	}

	public function dataRemoveNull(): array
	{
		return [
			[
				new MixedType(),
				MixedType::class,
				'mixed',
			],
			[
				new NullType(),
				NeverType::class,
				'*NEVER*',
			],
			[
				new VoidType(),
				VoidType::class,
				'void',
			],
			[
				new StringType(),
				StringType::class,
				'string',
			],
			[
				new UnionType([
					new StringType(),
					new IntegerType(),
					new NullType(),
				]),
				UnionType::class,
				'int|string',
			],
			[
				new UnionType([
					new StringType(),
					new IntegerType(),
				]),
				UnionType::class,
				'int|string',
			],
			[
				new UnionType([
					new IntersectionType([
						new IterableType(new MixedType(), new StringType()),
						new ObjectType('ArrayObject'),
					]),
					new NullType(),
				]),
				IntersectionType::class,
				'ArrayObject&iterable<string>',
			],
			[
				new IntersectionType([
					new IterableType(new MixedType(), new StringType()),
					new ObjectType('ArrayObject'),
				]),
				IntersectionType::class,
				'ArrayObject&iterable<string>',
			],
			[
				new UnionType([
					new ThisType('Foo'),
					new NullType(),
				]),
				ThisType::class,
				'$this(Foo)',
			],
			[
				new UnionType([
					new IterableType(new MixedType(), new StringType()),
					new NullType(),
				]),
				IterableType::class,
				'iterable<string>',
			],
		];
	}

	/**
	 * @dataProvider dataRemoveNull
	 * @param \PHPStan\Type\Type $type
	 * @param string $expectedTypeClass
	 * @param string $expectedTypeDescription
	 */
	public function testRemoveNull(
		Type $type,
		string $expectedTypeClass,
		string $expectedTypeDescription
	): void
	{
		$result = TypeCombinator::removeNull($type);
		$this->assertSame($expectedTypeDescription, $result->describe());
		$this->assertInstanceOf($expectedTypeClass, $result);
	}

	public function dataUnion(): array
	{
		return [
			[
				[
					new StringType(),
					new NullType(),
				],
				UnionType::class,
				'string|null',
			],
			[
				[
					new MixedType(),
					new IntegerType(),
				],
				MixedType::class,
				'mixed',
			],
			[
				[
					new ConstantBooleanType(true),
					new ConstantBooleanType(false),
				],
				BooleanType::class,
				'bool',
			],
			[
				[
					new StringType(),
					new IntegerType(),
				],
				UnionType::class,
				'int|string',
			],
			[
				[
					new UnionType([
						new StringType(),
						new IntegerType(),
					]),
					new StringType(),
				],
				UnionType::class,
				'int|string',
			],
			[
				[
					new UnionType([
						new StringType(),
						new IntegerType(),
					]),
					new ConstantBooleanType(true),
				],
				UnionType::class,
				'int|string|true',
			],
			[
				[
					new UnionType([
						new StringType(),
						new IntegerType(),
					]),
					new NullType(),
				],
				UnionType::class,
				'int|string|null',
			],
			[
				[
					new UnionType([
						new StringType(),
						new IntegerType(),
						new NullType(),
					]),
					new NullType(),
				],
				UnionType::class,
				'int|string|null',
			],
			[
				[
					new UnionType([
						new StringType(),
						new IntegerType(),
					]),
					new StringType(),
				],
				UnionType::class,
				'int|string',
			],
			[
				[
					new IntersectionType([
						new IterableType(new MixedType(), new IntegerType()),
						new ObjectType('ArrayObject'),
					]),
					new StringType(),
				],
				UnionType::class,
				'(ArrayObject&iterable<int>)|string',
			],
			[
				[
					new IntersectionType([
						new IterableType(new MixedType(), new IntegerType()),
						new ObjectType('ArrayObject'),
					]),
					new ArrayType(new MixedType(), new StringType()),
				],
				UnionType::class,
				'array<string>|(ArrayObject&iterable<int>)',
			],
			[
				[
						new UnionType([
						new ConstantBooleanType(true),
						new IntegerType(),
						]),
					new ArrayType(new MixedType(), new StringType()),
				],
				UnionType::class,
				'array<string>|int|true',
			],
			[
				[
					new UnionType([
						new ArrayType(new MixedType(), new ObjectType('Foo')),
						new ArrayType(new MixedType(), new ObjectType('Bar')),
					]),
					new ArrayType(new MixedType(), new MixedType()),
				],
				ArrayType::class,
				'array',
			],
			[
				[
					new IterableType(new MixedType(), new MixedType()),
					new ArrayType(new MixedType(), new StringType()),
				],
				IterableType::class,
				'iterable',
			],
			[
				[
					new IterableType(new MixedType(), new MixedType()),
					new ArrayType(new MixedType(), new MixedType()),
				],
				IterableType::class,
				'iterable',
			],
			[
				[
					new ArrayType(new MixedType(), new StringType()),
				],
				ArrayType::class,
				'array<string>',
			],
			[
				[
					new ObjectType('ArrayObject'),
					new ObjectType('ArrayIterator'),
					new ArrayType(new MixedType(), new StringType()),
				],
				UnionType::class,
				'array<string>|ArrayIterator|ArrayObject',
			],
			[
				[
					new ObjectType('ArrayObject'),
					new ObjectType('ArrayIterator'),
					new ArrayType(new MixedType(), new StringType()),
					new ArrayType(new MixedType(), new IntegerType()),
				],
				UnionType::class,
				'array<int|string>|ArrayIterator|ArrayObject',
			],
			[
				[
					new IntersectionType([
						new IterableType(new MixedType(), new IntegerType()),
						new ObjectType('ArrayObject'),
					]),
					new ArrayType(new MixedType(), new IntegerType()),
				],
				UnionType::class,
				'array<int>|(ArrayObject&iterable<int>)',
			],
			[
				[
					new ObjectType('UnknownClass'),
					new ObjectType('UnknownClass'),
				],
				ObjectType::class,
				'UnknownClass',
			],
			[
				[
					new IntersectionType([
						new ObjectType('DateTimeInterface'),
						new ObjectType('Traversable'),
					]),
					new IntersectionType([
						new ObjectType('DateTimeInterface'),
						new ObjectType('Traversable'),
					]),
				],
				IntersectionType::class,
				'DateTimeInterface&Traversable',
			],
			[
				[
					new ObjectType('UnknownClass'),
					new ObjectType('UnknownClass'),
				],
				ObjectType::class,
				'UnknownClass',
			],
			[
				[
					new StringType(),
					new NeverType(),
				],
				StringType::class,
				'string',
			],
			[
				[
					new IntersectionType([
						new ObjectType('ArrayObject'),
						new IterableType(new MixedType(), new StringType()),
					]),
					new NeverType(),
				],
				IntersectionType::class,
				'ArrayObject&iterable<string>',
			],
			[
				[
					new IterableType(new MixedType(), new MixedType()),
					new IterableType(new MixedType(), new StringType()),
				],
				IterableType::class,
				'iterable',
			],
			[
				[
					new IterableType(new MixedType(), new IntegerType()),
					new IterableType(new MixedType(), new StringType()),
				],
				IterableType::class,
				'iterable<int|string>',
			],
			[
				[
					new IterableType(new MixedType(), new IntegerType()),
					new IterableType(new IntegerType(), new StringType()),
				],
				IterableType::class,
				'iterable<int|string>',
			],
			[
				[
					new IterableType(new StringType(), new IntegerType()),
					new IterableType(new IntegerType(), new StringType()),
				],
				IterableType::class,
				'iterable<int|string, int|string>',
			],
			[
				[
					new ArrayType(new MixedType(), new MixedType()),
					new ArrayType(new MixedType(), new StringType()),
				],
				ArrayType::class,
				'array',
			],
			[
				[
					new ArrayType(new MixedType(), new IntegerType()),
					new ArrayType(new MixedType(), new StringType()),
				],
				ArrayType::class,
				'array<int|string>',
			],
			[
				[
					new ArrayType(new MixedType(), new IntegerType()),
					new ArrayType(new IntegerType(), new StringType()),
				],
				ArrayType::class,
				'array<int|string>',
			],
			[
				[
					new ArrayType(new StringType(), new IntegerType()),
					new ArrayType(new IntegerType(), new StringType()),
				],
				ArrayType::class,
				'array<int|string, int|string>',
			],
			[
				[
					new UnionType([
						new StringType(),
						new NullType(),
					]),
					new UnionType([
						new StringType(),
						new NullType(),
					]),
					new UnionType([
						new ObjectType('Unknown'),
						new NullType(),
					]),
				],
				UnionType::class,
				'string|Unknown|null',
			],
			[
				[
					new ObjectType(\RecursionCallable\Foo::class),
					new CallableType(),
				],
				UnionType::class,
				'callable|RecursionCallable\Foo',
			],
			[
				[
					new IntegerType(),
					new ConstantIntegerType(1),
				],
				IntegerType::class,
				'int',
			],
			[
				[
					new ConstantIntegerType(1),
					new ConstantIntegerType(1),
				],
				ConstantIntegerType::class,
				'int(1)',
			],
			[
				[
					new ConstantIntegerType(1),
					new ConstantIntegerType(2),
				],
				UnionType::class,
				'int(1)|int(2)',
			],
			[
				[
					new FloatType(),
					new ConstantFloatType(1.0),
				],
				FloatType::class,
				'float',
			],
			[
				[
					new ConstantFloatType(1.0),
					new ConstantFloatType(1.0),
				],
				ConstantFloatType::class,
				'float(1.000000)',
			],
			[
				[
					new ConstantFloatType(1.0),
					new ConstantFloatType(2.0),
				],
				UnionType::class,
				'float(1.000000)|float(2.000000)',
			],
			[
				[
					new StringType(),
					new ConstantStringType('A'),
				],
				StringType::class,
				'string',
			],
			[
				[
					new ConstantStringType('A'),
					new ConstantStringType('A'),
				],
				ConstantStringType::class,
				'string',
			],
			[
				[
					new ConstantStringType('A'),
					new ConstantStringType('B'),
				],
				UnionType::class,
				'string',
			],
			[
				[
					new BooleanType(),
					new ConstantBooleanType(true),
				],
				BooleanType::class,
				'bool',
			],
			[
				[
					new ConstantBooleanType(true),
					new ConstantBooleanType(true),
				],
				ConstantBooleanType::class,
				'true',
			],
			[
				[
					new ConstantBooleanType(true),
					new ConstantBooleanType(false),
				],
				BooleanType::class,
				'bool',
			],
		];
	}

	/**
	 * @dataProvider dataUnion
	 * @param \PHPStan\Type\Type[] $types
	 * @param string $expectedTypeClass
	 * @param string $expectedTypeDescription
	 */
	public function testUnion(
		array $types,
		string $expectedTypeClass,
		string $expectedTypeDescription
	): void
	{
		$actualType = TypeCombinator::union(...$types);

		$this->assertSame(
			$expectedTypeDescription,
			$actualType->describe(),
			sprintf('union(%s)', implode(', ', array_map(
				function (Type $type): string {
					return $type->describe();
				},
				$types
			)))
		);

		$this->assertInstanceOf($expectedTypeClass, $actualType);
	}

	/**
	 * @dataProvider dataUnion
	 * @param \PHPStan\Type\Type[] $types
	 * @param string $expectedTypeClass
	 * @param string $expectedTypeDescription
	 */
	public function testUnionInversed(
		array $types,
		string $expectedTypeClass,
		string $expectedTypeDescription
	): void
	{
		$result = TypeCombinator::union(...array_reverse($types));
		$this->assertSame($expectedTypeDescription, $result->describe());
		$this->assertInstanceOf($expectedTypeClass, $result);
	}

	public function dataIntersect(): array
	{
		return [
			[
				[
					new IterableType(new MixedType(), new StringType()),
					new ObjectType('ArrayObject'),
				],
				IntersectionType::class,
				'ArrayObject&iterable<string>',
			],
			[
				[
					new IterableType(new MixedType(), new StringType()),
					new ArrayType(new MixedType(), new StringType()),
				],
				ArrayType::class,
				'array<string>',
			],
			[
				[
					new ObjectType('Foo'),
					new StaticType('Foo'),
				],
				StaticType::class,
				'static(Foo)',
			],
			[
				[
					new VoidType(),
					new MixedType(),
				],
				VoidType::class,
				'void',
			],

			[
				[
					new ObjectType('UnknownClass'),
					new ObjectType('UnknownClass'),
				],
				ObjectType::class,
				'UnknownClass',
			],
			[
				[
					new UnionType([new ObjectType('UnknownClassA'), new ObjectType('UnknownClassB')]),
					new UnionType([new ObjectType('UnknownClassA'), new ObjectType('UnknownClassB')]),
				],
				UnionType::class,
				'UnknownClassA|UnknownClassB',
			],
			[
				[
					new ConstantBooleanType(true),
					new BooleanType(),
				],
				ConstantBooleanType::class,
				'true',
			],
			[
				[
					new StringType(),
					new NeverType(),
				],
				NeverType::class,
				'*NEVER*',
			],
			[
				[
					new ObjectType('Iterator'),
					new ObjectType('Countable'),
					new ObjectType('Traversable'),
				],
				IntersectionType::class,
				'Countable&Iterator',
			],
			[
				[
					new ObjectType('Iterator'),
					new ObjectType('Traversable'),
					new ObjectType('Countable'),
				],
				IntersectionType::class,
				'Countable&Iterator',
			],
			[
				[
					new ObjectType('Traversable'),
					new ObjectType('Iterator'),
					new ObjectType('Countable'),
				],
				IntersectionType::class,
				'Countable&Iterator',
			],
			[
				[
					new IterableType(new MixedType(), new MixedType()),
					new IterableType(new MixedType(), new StringType()),
				],
				IterableType::class,
				'iterable<string>',
			],
			[
				[
					new ArrayType(new MixedType(), new MixedType()),
					new IterableType(new MixedType(), new StringType()),
				],
				IntersectionType::class,
				'array&iterable<string>', // this is correct but 'array<string>' would be better
			],
			[
				[
					new MixedType(),
					new IterableType(new MixedType(), new MixedType()),
				],
				IterableType::class,
				'iterable',
			],
		];
	}

	/**
	 * @dataProvider dataIntersect
	 * @param \PHPStan\Type\Type[] $types
	 * @param string $expectedTypeClass
	 * @param string $expectedTypeDescription
	 */
	public function testIntersect(
		array $types,
		string $expectedTypeClass,
		string $expectedTypeDescription
	): void
	{
		$result = TypeCombinator::intersect(...$types);
		$this->assertInstanceOf($expectedTypeClass, $result);
		$this->assertSame($expectedTypeDescription, $result->describe());
	}

	/**
	 * @dataProvider dataIntersect
	 * @param \PHPStan\Type\Type[] $types
	 * @param string $expectedTypeClass
	 * @param string $expectedTypeDescription
	 */
	public function testIntersectInversed(
		array $types,
		string $expectedTypeClass,
		string $expectedTypeDescription
	): void
	{
		$result = TypeCombinator::intersect(...array_reverse($types));
		$this->assertInstanceOf($expectedTypeClass, $result);
		$this->assertSame($expectedTypeDescription, $result->describe());
	}

	public function dataRemove(): array
	{
		return [
			[
				new ConstantBooleanType(true),
				new ConstantBooleanType(true),
				NeverType::class,
				'*NEVER*',
			],
			[
				new UnionType([
					new IntegerType(),
					new ConstantBooleanType(true),
				]),
				new ConstantBooleanType(true),
				IntegerType::class,
				'int',
			],
			[
				new UnionType([
					new ObjectType('Foo'),
					new ObjectType('Bar'),
				]),
				new ObjectType('Foo'),
				ObjectType::class,
				'Bar',
			],
			[
				new UnionType([
					new ObjectType('Foo'),
					new ObjectType('Bar'),
					new ObjectType('Baz'),
				]),
				new ObjectType('Foo'),
				UnionType::class,
				'Bar|Baz',
			],
			[
				new UnionType([
					new ArrayType(new MixedType(), new StringType()),
					new ArrayType(new MixedType(), new IntegerType()),
					new ObjectType('ArrayObject'),
				]),
				new ArrayType(new MixedType(), new IntegerType()),
				UnionType::class,
				'array<string>|ArrayObject',
			],
			[
				new ConstantBooleanType(true),
				new ConstantBooleanType(false),
				ConstantBooleanType::class,
				'true',
			],
			[
				new ConstantBooleanType(false),
				new ConstantBooleanType(true),
				ConstantBooleanType::class,
				'false',
			],
			[
				new ConstantBooleanType(true),
				new BooleanType(),
				NeverType::class,
				'*NEVER*',
			],
			[
				new ConstantBooleanType(false),
				new BooleanType(),
				NeverType::class,
				'*NEVER*',
			],
			[
				new BooleanType(),
				new ConstantBooleanType(true),
				ConstantBooleanType::class,
				'false',
			],
			[
				new BooleanType(),
				new ConstantBooleanType(false),
				ConstantBooleanType::class,
				'true',
			],
			[
				new BooleanType(),
				new BooleanType(),
				NeverType::class,
				'*NEVER*',
			],
			[
				new UnionType([
					new ConstantBooleanType(true),
					new IntegerType(),
				]),
				new BooleanType(),
				IntegerType::class,
				'int',
			],
			[
				new UnionType([
					new ConstantBooleanType(false),
					new IntegerType(),
				]),
				new BooleanType(),
				IntegerType::class,
				'int',
			],
			[
				new UnionType([
					new BooleanType(),
					new IntegerType(),
				]),
				new ConstantBooleanType(true),
				UnionType::class,
				'false|int',
			],
			[
				new UnionType([
					new BooleanType(),
					new IntegerType(),
				]),
				new ConstantBooleanType(false),
				UnionType::class,
				'int|true',
			],
			[
				new UnionType([
					new StringType(),
					new IntegerType(),
					new NullType(),
				]),
				new UnionType([
					new NullType(),
					new StringType(),
				]),
				IntegerType::class,
				'int',
			],
		];
	}

	/**
	 * @dataProvider dataRemove
	 * @param \PHPStan\Type\Type $fromType
	 * @param \PHPStan\Type\Type $type
	 * @param string $expectedTypeClass
	 * @param string $expectedTypeDescription
	 */
	public function testRemove(
		Type $fromType,
		Type $type,
		string $expectedTypeClass,
		string $expectedTypeDescription
	): void
	{
		$result = TypeCombinator::remove($fromType, $type);
		$this->assertSame($expectedTypeDescription, $result->describe());
		$this->assertInstanceOf($expectedTypeClass, $result);
	}

}
