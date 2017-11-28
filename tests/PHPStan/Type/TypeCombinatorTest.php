<?php declare(strict_types = 1);

namespace PHPStan\Type;

class TypeCombinatorTest extends \PHPStan\Testing\TestCase
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
					new IterableIterableType(new MixedType(), new StringType()),
					new ObjectType('ArrayObject'),
				]),
				UnionType::class,
				'(ArrayObject&iterable<string>)|null',
			],
			[
				new UnionType([
					new IntersectionType([
						new IterableIterableType(new MixedType(), new StringType()),
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
						new IterableIterableType(new MixedType(), new StringType()),
						new ObjectType('ArrayObject'),
					]),
					new NullType(),
				]),
				IntersectionType::class,
				'ArrayObject&iterable<string>',
			],
			[
				new IntersectionType([
					new IterableIterableType(new MixedType(), new StringType()),
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
					new IterableIterableType(new MixedType(), new StringType()),
					new NullType(),
				]),
				IterableIterableType::class,
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
					new TrueBooleanType(),
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
						new IterableIterableType(new MixedType(), new IntegerType()),
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
						new IterableIterableType(new MixedType(), new IntegerType()),
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
						new TrueBooleanType(),
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
					new IterableIterableType(new MixedType(), new MixedType()),
					new ArrayType(new MixedType(), new StringType()),
				],
				IterableIterableType::class,
				'iterable',
			],
			[
				[
					new IterableIterableType(new MixedType(), new MixedType()),
					new ArrayType(new MixedType(), new MixedType()),
				],
				IterableIterableType::class,
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
						new IterableIterableType(new MixedType(), new IntegerType()),
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
						new IterableIterableType(new MixedType(), new StringType()),
					]),
					new NeverType(),
				],
				IntersectionType::class,
				'ArrayObject&iterable<string>',
			],
			[
				[
					new IterableIterableType(new MixedType(), new MixedType()),
					new IterableIterableType(new MixedType(), new StringType()),
				],
				IterableIterableType::class,
				'iterable',
			],
			[
				[
					new IterableIterableType(new MixedType(), new IntegerType()),
					new IterableIterableType(new MixedType(), new StringType()),
				],
				IterableIterableType::class,
				'iterable<int|string>',
			],
			[
				[
					new IterableIterableType(new MixedType(), new IntegerType()),
					new IterableIterableType(new IntegerType(), new StringType()),
				],
				IterableIterableType::class,
				'iterable<int|string>',
			],
			[
				[
					new IterableIterableType(new StringType(), new IntegerType()),
					new IterableIterableType(new IntegerType(), new StringType()),
				],
				IterableIterableType::class,
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
					new IterableIterableType(new MixedType(), new StringType()),
					new ObjectType('ArrayObject'),
				],
				IntersectionType::class,
				'ArrayObject&iterable<string>',
			],
			[
				[
					new IterableIterableType(new MixedType(), new StringType()),
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
					new IterableIterableType(new MixedType(), new MixedType()),
					new IterableIterableType(new MixedType(), new StringType()),
				],
				IterableIterableType::class,
				'iterable<string>',
			],
			[
				[
					new ArrayType(new MixedType(), new MixedType()),
					new IterableIterableType(new MixedType(), new StringType()),
				],
				IntersectionType::class,
				'array&iterable<string>', // this is correct but 'array<string>' would be better
			],
			[
				[
					new MixedType(),
					new IterableIterableType(new MixedType(), new MixedType()),
				],
				IterableIterableType::class,
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
				NeverType::class,
				'*NEVER*',
			],
			[
				new UnionType([
					new IntegerType(),
					new TrueBooleanType(),
				]),
				new TrueBooleanType(),
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
				NeverType::class,
				'*NEVER*',
			],
			[
				new FalseBooleanType(),
				new TrueOrFalseBooleanType(),
				NeverType::class,
				'*NEVER*',
			],
			[
				new TrueOrFalseBooleanType(),
				new TrueBooleanType(),
				FalseBooleanType::class,
				'false',
			],
			[
				new TrueOrFalseBooleanType(),
				new FalseBooleanType(),
				TrueBooleanType::class,
				'true',
			],
			[
				new TrueOrFalseBooleanType(),
				new TrueOrFalseBooleanType(),
				NeverType::class,
				'*NEVER*',
			],
			[
				new UnionType([
					new TrueBooleanType(),
					new IntegerType(),
				]),
				new TrueOrFalseBooleanType(),
				IntegerType::class,
				'int',
			],
			[
				new UnionType([
					new FalseBooleanType(),
					new IntegerType(),
				]),
				new TrueOrFalseBooleanType(),
				IntegerType::class,
				'int',
			],
			[
				new UnionType([
					new TrueOrFalseBooleanType(),
					new IntegerType(),
				]),
				new TrueBooleanType(),
				UnionType::class,
				'false|int',
			],
			[
				new UnionType([
					new TrueOrFalseBooleanType(),
					new IntegerType(),
				]),
				new FalseBooleanType(),
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
	)
	{
		$result = TypeCombinator::remove($fromType, $type);
		$this->assertSame($expectedTypeDescription, $result->describe());
		$this->assertInstanceOf($expectedTypeClass, $result);
	}

}
