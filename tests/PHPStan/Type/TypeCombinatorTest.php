<?php declare(strict_types = 1);

namespace PHPStan\Type;

class TypeCombinatorTest extends \PHPStan\TestCase
{

	protected function setUp()
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
				CommonUnionType::class,
				'void|null',
			],
			[
				new StringType(),
				CommonUnionType::class,
				'string|null',
			],
			[
				new CommonUnionType([
					new StringType(),
					new IntegerType(),
				]),
				CommonUnionType::class,
				'int|string|null',
			],
			[
				new CommonUnionType([
					new StringType(),
					new IntegerType(),
					new NullType(),
				]),
				CommonUnionType::class,
				'int|string|null',
			],
			[
				new IntersectionType([
					new IterableIterableType(new StringType()),
					new ObjectType('ArrayObject'),
				]),
				CommonUnionType::class,
				'(ArrayObject&iterable(string[]))|null',
			],
			[
				new CommonUnionType([
					new IntersectionType([
						new IterableIterableType(new StringType()),
						new ObjectType('ArrayObject'),
					]),
					new NullType(),
				]),
				CommonUnionType::class,
				'(ArrayObject&iterable(string[]))|null',
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
	)
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
	)
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
				MixedType::class,
				'mixed',
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
				new CommonUnionType([
					new StringType(),
					new IntegerType(),
					new NullType(),
				]),
				CommonUnionType::class,
				'int|string',
			],
			[
				new CommonUnionType([
					new StringType(),
					new IntegerType(),
				]),
				CommonUnionType::class,
				'int|string',
			],
			[
				new CommonUnionType([
					new IntersectionType([
						new IterableIterableType(new StringType()),
						new ObjectType('ArrayObject'),
					]),
					new NullType(),
				]),
				IntersectionType::class,
				'ArrayObject&iterable(string[])',
			],
			[
				new IntersectionType([
					new IterableIterableType(new StringType()),
					new ObjectType('ArrayObject'),
				]),
				IntersectionType::class,
				'ArrayObject&iterable(string[])',
			],
			[
				new CommonUnionType([
					new ThisType('Foo'),
					new NullType(),
				]),
				ThisType::class,
				'$this(Foo)',
			],
			[
				new CommonUnionType([
					new IterableIterableType(new StringType()),
					new NullType(),
				]),
				IterableIterableType::class,
				'iterable(string[])',
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
	)
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
				CommonUnionType::class,
				'string|null',
			],
			[
				[
					new TrueBooleanType(),
					new FalseBooleanType(),
				],
				TrueOrFalseBooleanType::class,
				'bool',
			],
			[
				[
					new StringType(),
					new IntegerType(),
				],
				CommonUnionType::class,
				'int|string',
			],
			[
				[
					new CommonUnionType([
						new StringType(),
						new IntegerType(),
					]),
					new StringType(),
				],
				CommonUnionType::class,
				'int|string',
			],
			[
				[
					new CommonUnionType([
						new StringType(),
						new IntegerType(),
					]),
					new TrueBooleanType(),
				],
				CommonUnionType::class,
				'int|string|true',
			],
			[
				[
					new CommonUnionType([
						new StringType(),
						new IntegerType(),
					]),
					new NullType(),
				],
				CommonUnionType::class,
				'int|string|null',
			],
			[
				[
					new CommonUnionType([
						new StringType(),
						new IntegerType(),
						new NullType(),
					]),
					new NullType(),
				],
				CommonUnionType::class,
				'int|string|null',
			],
			[
				[
					new CommonUnionType([
						new StringType(),
						new IntegerType(),
					]),
					new StringType(),
				],
				CommonUnionType::class,
				'int|string',
			],
			[
				[
					new IntersectionType([
						new IterableIterableType(new IntegerType()),
						new ObjectType('ArrayObject'),
					]),
					new StringType(),
				],
				CommonUnionType::class,
				'(ArrayObject&iterable(int[]))|string',
			],
			[
				[
					new IntersectionType([
						new IterableIterableType(new IntegerType()),
						new ObjectType('ArrayObject'),
					]),
					new ArrayType(new StringType()),
				],
				CommonUnionType::class,
				'(ArrayObject&iterable(int[]))|string[]',
			],
			[
				[
						new CommonUnionType([
						new TrueBooleanType(),
						new IntegerType(),
						]),
					new ArrayType(new StringType()),
				],
				CommonUnionType::class,
				'int|string[]|true',
			],
			[
				[
					new CommonUnionType([
						new ArrayType(new ObjectType('Foo')),
						new ArrayType(new ObjectType('Bar')),
					]),
					new ArrayType(new MixedType()),
				],
				ArrayType::class,
				'mixed[]',
			],
			[
				[
					new IterableIterableType(new MixedType()),
					new ArrayType(new StringType()),
				],
				IterableIterableType::class,
				'iterable(mixed[])',
			],
			[
				[
					new IterableIterableType(new MixedType()),
					new ArrayType(new MixedType()),
				],
				IterableIterableType::class,
				'iterable(mixed[])',
			],
			[
				[
					new ArrayType(new StringType()),
				],
				ArrayType::class,
				'string[]',
			],
			[
				[
					new ObjectType('ArrayObject'),
					new ObjectType('ArrayIterator'),
					new ArrayType(new StringType()),
				],
				CommonUnionType::class,
				'ArrayIterator|ArrayObject|string[]',
			],
			[
				[
					new ObjectType('ArrayObject'),
					new ObjectType('ArrayIterator'),
					new ArrayType(new StringType()),
					new ArrayType(new IntegerType()),
				],
				CommonUnionType::class,
				'ArrayIterator|ArrayObject|int[]|string[]',
			],
			[
				[
					new IntersectionType([
						new IterableIterableType(new IntegerType()),
						new ObjectType('ArrayObject'),
					]),
					new ArrayType(new IntegerType()),
				],
				CommonUnionType::class,
				'(ArrayObject&iterable(int[]))|int[]',
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
						new IterableIterableType(new StringType()),
					]),
					new NeverType(),
				],
				IntersectionType::class,
				'ArrayObject&iterable(string[])',
			],
			[
				[
					new IterableIterableType(new MixedType()),
					new IterableIterableType(new StringType()),
				],
				IterableIterableType::class,
				'iterable(mixed[])',
			],
			[
				[
					new IterableIterableType(new IntegerType()),
					new IterableIterableType(new StringType()),
				],
				CommonUnionType::class,
				'iterable(int[])|iterable(string[])', // this is correct but 'iterable(int|string[])' would be better
			],
			[
				[
					new CommonUnionType([
						new StringType(),
						new NullType(),
					]),
					new CommonUnionType([
						new StringType(),
						new NullType(),
					]),
					new CommonUnionType([
						new ObjectType('Unknown'),
						new NullType(),
					]),
				],
				CommonUnionType::class,
				'string|Unknown|null',
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
	)
	{
		$result = TypeCombinator::union(...$types);
		$this->assertSame($expectedTypeDescription, $result->describe());
		$this->assertInstanceOf($expectedTypeClass, $result);
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
	)
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
					new IterableIterableType(new StringType()),
					new ObjectType('ArrayObject'),
				],
				IntersectionType::class,
				'ArrayObject&iterable(string[])',
			],
			[
				[
					new IterableIterableType(new StringType()),
					new ArrayType(new StringType()),
				],
				ArrayType::class,
				'string[]',
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
					new CommonUnionType([new ObjectType('UnknownClassA'), new ObjectType('UnknownClassB')]),
					new CommonUnionType([new ObjectType('UnknownClassA'), new ObjectType('UnknownClassB')]),
				],
				CommonUnionType::class,
				'UnknownClassA|UnknownClassB',
			],
			[
				[
					new TrueBooleanType(),
					new TrueOrFalseBooleanType(),
				],
				TrueBooleanType::class,
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
					new IterableIterableType(new MixedType()),
					new IterableIterableType(new StringType()),
				],
				IterableIterableType::class,
				'iterable(string[])',
			],
			[
				[
					new ArrayType(new MixedType()),
					new IterableIterableType(new StringType()),
				],
				IntersectionType::class,
				'iterable(string[])&mixed[]', // this is correct but 'string[]' would be better
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
	)
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
	)
	{
		$result = TypeCombinator::intersect(...array_reverse($types));
		$this->assertInstanceOf($expectedTypeClass, $result);
		$this->assertSame($expectedTypeDescription, $result->describe());
	}

	public function dataRemove(): array
	{
		return [
			[
				new TrueBooleanType(),
				new TrueBooleanType(),
				MixedType::class,
				'mixed',
			],
			[
				new CommonUnionType([
					new IntegerType(),
					new TrueBooleanType(),
				]),
				new TrueBooleanType(),
				IntegerType::class,
				'int',
			],
			[
				new CommonUnionType([
					new ObjectType('Foo'),
					new ObjectType('Bar'),
				]),
				new ObjectType('Foo'),
				ObjectType::class,
				'Bar',
			],
			[
				new CommonUnionType([
					new ObjectType('Foo'),
					new ObjectType('Bar'),
					new ObjectType('Baz'),
				]),
				new ObjectType('Foo'),
				CommonUnionType::class,
				'Bar|Baz',
			],
			[
				new CommonUnionType([
					new ArrayType(new StringType()),
					new ArrayType(new IntegerType()),
					new ObjectType('ArrayObject'),
				]),
				new ArrayType(new IntegerType()),
				CommonUnionType::class,
				'ArrayObject|string[]',
			],
			[
				new TrueBooleanType(),
				new FalseBooleanType(),
				TrueBooleanType::class,
				'true',
			],
			[
				new FalseBooleanType(),
				new TrueBooleanType(),
				FalseBooleanType::class,
				'false',
			],
			[
				new TrueBooleanType(),
				new TrueOrFalseBooleanType(),
				MixedType::class,
				'mixed',
			],
			[
				new FalseBooleanType(),
				new TrueOrFalseBooleanType(),
				MixedType::class,
				'mixed',
			],
			[
				new CommonUnionType([
					new TrueBooleanType(),
					new IntegerType(),
				]),
				new TrueOrFalseBooleanType(),
				IntegerType::class,
				'int',
			],
			[
				new CommonUnionType([
					new FalseBooleanType(),
					new IntegerType(),
				]),
				new TrueOrFalseBooleanType(),
				IntegerType::class,
				'int',
			],
			[
				new CommonUnionType([
					new StringType(),
					new IntegerType(),
					new NullType(),
				]),
				new CommonUnionType([
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
	)
	{
		$result = TypeCombinator::remove($fromType, $type);
		$this->assertSame($expectedTypeDescription, $result->describe());
		$this->assertInstanceOf($expectedTypeClass, $result);
	}

}
