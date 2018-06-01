<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;

class TypeCombinatorTest extends \PHPStan\Testing\TestCase
{

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
		$this->assertSame($expectedTypeDescription, $result->describe(VerbosityLevel::value()));
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
		$this->assertSame($expectedTypeDescription, $result->describe(VerbosityLevel::value()));
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
		$this->assertSame($expectedTypeDescription, $result->describe(VerbosityLevel::value()));
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
				'1',
			],
			[
				[
					new ConstantIntegerType(1),
					new ConstantIntegerType(2),
				],
				UnionType::class,
				'1|2',
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
				'1.0',
			],
			[
				[
					new ConstantFloatType(1.0),
					new ConstantFloatType(2.0),
				],
				UnionType::class,
				'1.0|2.0',
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
				'\'A\'',
			],
			[
				[
					new ConstantStringType('A'),
					new ConstantStringType('B'),
				],
				UnionType::class,
				'\'A\'|\'B\'',
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
			[
				[
					new ObjectType(\Closure::class),
					new ClosureType([], new MixedType(), false),
				],
				ObjectType::class,
				'Closure',
			],
			[
				[
					new ClosureType([], new MixedType(), false),
					new CallableType(),
				],
				CallableType::class,
				'callable',
			],
			[
				// same keys - can remain ConstantArrayType
				[
					new ConstantArrayType([
						new ConstantStringType('foo'),
						new ConstantStringType('bar'),
					], [
						new ObjectType(\DateTimeImmutable::class),
						new IntegerType(),
					]),
					new ConstantArrayType([
						new ConstantStringType('foo'),
						new ConstantStringType('bar'),
					], [
						new NullType(),
						new StringType(),
					]),
				],
				ConstantArrayType::class,
				'array(\'foo\' => DateTimeImmutable|null, \'bar\' => int|string)',
			],
			[
				[
					new ConstantArrayType([
						new ConstantStringType('foo'),
						new ConstantStringType('bar'),
					], [
						new ObjectType(\DateTimeImmutable::class),
						new IntegerType(),
					]),
					new ConstantArrayType([
						new ConstantStringType('foo'),
					], [
						new NullType(),
					]),
				],
				UnionType::class,
				'array(\'foo\' => DateTimeImmutable|null, ?\'bar\' => int)',
			],
			[
				[
					new ConstantArrayType([
						new ConstantStringType('foo'),
						new ConstantStringType('bar'),
					], [
						new ObjectType(\DateTimeImmutable::class),
						new IntegerType(),
					]),
					new ConstantArrayType([
						new ConstantStringType('foo'),
						new ConstantStringType('bar'),
						new ConstantStringType('baz'),
					], [
						new NullType(),
						new StringType(),
						new IntegerType(),
					]),
				],
				UnionType::class,
				'array(\'foo\' => DateTimeImmutable|null, \'bar\' => int|string, ?\'baz\' => int)',
			],
			[
				[
					new ArrayType(
						new IntegerType(),
						new ObjectType(\stdClass::class)
					),
					new ConstantArrayType([
						new ConstantStringType('foo'),
						new ConstantStringType('bar'),
					], [
						new ObjectType(\DateTimeImmutable::class),
						new IntegerType(),
					]),
				],
				ArrayType::class,
				'array<\'bar\'|\'foo\'|int, DateTimeImmutable|int|stdClass>',
			],
			[
				[
					new ConstantArrayType([new ConstantIntegerType(0)], [new StringType()]),
					new ArrayType(new MixedType(), new StringType()),
				],
				ArrayType::class,
				'array<string>',
			],
			[
				[
					new ConstantArrayType([], []),
					new ConstantArrayType([new ConstantIntegerType(0)], [new StringType()]),
					new ArrayType(new MixedType(), new StringType()),
				],
				ArrayType::class,
				'array<string>',
			],
			[
				[
					new UnionType([new IntegerType(), new StringType()]),
					new BenevolentUnionType([new IntegerType(), new StringType()]),
				],
				UnionType::class,
				'int|string',
			],
			[
				[
					new IntegerType(),
					new BenevolentUnionType([new IntegerType(), new StringType()]),
				],
				UnionType::class,
				'int|string',
			],
			[
				[
					new StringType(),
					new BenevolentUnionType([new IntegerType(), new StringType()]),
				],
				UnionType::class,
				'int|string',
			],
			[
				[
					new UnionType([new IntegerType(), new StringType(), new FloatType()]),
					new BenevolentUnionType([new IntegerType(), new StringType()]),
				],
				UnionType::class,
				'float|int|string',
			],
			[
				[
					new UnionType([new StringType(), new FloatType()]),
					new BenevolentUnionType([new IntegerType(), new StringType()]),
				],
				UnionType::class,
				'float|int|string',
			],
			[
				[
					new UnionType([new IntegerType(), new FloatType()]),
					new BenevolentUnionType([new IntegerType(), new StringType()]),
				],
				UnionType::class,
				'float|int|string',
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
			$actualType->describe(VerbosityLevel::value()),
			sprintf('union(%s)', implode(', ', array_map(
				function (Type $type): string {
					return $type->describe(VerbosityLevel::value());
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
		$this->assertSame($expectedTypeDescription, $result->describe(VerbosityLevel::value()));
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
			[
				[
					new IntegerType(),
					new BenevolentUnionType([new IntegerType(), new StringType()]),
				],
				IntegerType::class,
				'int',
			],
			[
				[
					new ConstantIntegerType(1),
					new BenevolentUnionType([new IntegerType(), new StringType()]),
				],
				ConstantIntegerType::class,
				'1',
			],
			[
				[
					new ConstantStringType('foo'),
					new BenevolentUnionType([new IntegerType(), new StringType()]),
				],
				ConstantStringType::class,
				'\'foo\'',
			],
			[
				[
					new StringType(),
					new BenevolentUnionType([new IntegerType(), new StringType()]),
				],
				StringType::class,
				'string',
			],
			[
				[
					new UnionType([new StringType(), new IntegerType()]),
					new BenevolentUnionType([new IntegerType(), new StringType()]),
				],
				UnionType::class,
				'int|string',
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
		$this->assertSame($expectedTypeDescription, $result->describe(VerbosityLevel::value()));
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
		$this->assertSame($expectedTypeDescription, $result->describe(VerbosityLevel::value()));
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
				'int|false',
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
			[
				new IterableType(new MixedType(), new MixedType()),
				new ArrayType(new MixedType(), new MixedType()),
				ObjectType::class,
				'Traversable',
			],
			[
				new IterableType(new MixedType(), new MixedType()),
				new ObjectType(\Traversable::class),
				ArrayType::class,
				'array',
			],
			[
				new BenevolentUnionType([new IntegerType(), new StringType()]),
				new StringType(),
				IntegerType::class,
				'int',
			],
			[
				new BenevolentUnionType([new IntegerType(), new StringType()]),
				new IntegerType(),
				StringType::class,
				'string',
			],
			[
				new BenevolentUnionType([new IntegerType(), new StringType()]),
				new ConstantStringType('foo'),
				UnionType::class,
				'int|string',
			],
			[
				new BenevolentUnionType([new IntegerType(), new StringType()]),
				new ConstantIntegerType(1),
				UnionType::class,
				'int|string',
			],
			[
				new BenevolentUnionType([new IntegerType(), new StringType()]),
				new UnionType([new IntegerType(), new StringType()]),
				NeverType::class,
				'*NEVER*',
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
		$this->assertSame($expectedTypeDescription, $result->describe(VerbosityLevel::value()));
		$this->assertInstanceOf($expectedTypeClass, $result);
	}

}
