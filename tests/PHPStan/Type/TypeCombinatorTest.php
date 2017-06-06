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
				VoidType::class,
				'void',
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
				new UnionIterableType(new StringType(), [
					new ObjectType('ArrayObject'),
				]),
				UnionIterableType::class,
				'string[]|ArrayObject|null',
			],
			[
				new UnionIterableType(new StringType(), [
					new ObjectType('ArrayObject'),
					new NullType(),
				]),
				UnionIterableType::class,
				'string[]|ArrayObject|null',
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
		$this->assertInstanceOf($expectedTypeClass, $result);
		$this->assertSame($expectedTypeDescription, $result->describe());
	}

	/**
	 * @dataProvider dataAddNull
	 * @param \PHPStan\Type\Type $type
	 * @param string $expectedTypeClass
	 * @param string $expectedTypeDescription
	 */
	public function testCombineAddNull(
		Type $type,
		string $expectedTypeClass,
		string $expectedTypeDescription
	)
	{
		$result = TypeCombinator::combine($type, new NullType());
		$this->assertInstanceOf($expectedTypeClass, $result);
		$this->assertSame($expectedTypeDescription, $result->describe());
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
				new UnionIterableType(new StringType(), [
					new ObjectType('ArrayObject'),
					new NullType(),
				]),
				UnionIterableType::class,
				'string[]|ArrayObject',
			],
			[
				new UnionIterableType(new StringType(), [
					new ObjectType('ArrayObject'),
				]),
				UnionIterableType::class,
				'string[]|ArrayObject',
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
				new UnionIterableType(
					new StringType(),
					[
						new NullType(),
					]
				),
				ArrayType::class,
				'string[]',
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
		$this->assertInstanceOf($expectedTypeClass, $result);
		$this->assertSame($expectedTypeDescription, $result->describe());
	}

	public function dataCombine(): array
	{
		return [
			[
				new StringType(),
				new NullType(),
				CommonUnionType::class,
				'string|null',
			],
			[
				new StringType(),
				new IntegerType(),
				CommonUnionType::class,
				'int|string',
			],
			[
				new CommonUnionType([
					new StringType(),
					new IntegerType(),
				]),
				new StringType(),
				CommonUnionType::class,
				'int|string',
			],
			[
				new CommonUnionType([
					new StringType(),
					new IntegerType(),
				]),
				new TrueBooleanType(),
				CommonUnionType::class,
				'int|string|true',
			],
			[
				new CommonUnionType([
					new StringType(),
					new IntegerType(),
				]),
				new NullType(),
				CommonUnionType::class,
				'int|string|null',
			],
			[
				new CommonUnionType([
					new StringType(),
					new IntegerType(),
					new NullType(),
				]),
				new NullType(),
				CommonUnionType::class,
				'int|string|null',
			],
			[
				new CommonUnionType([
					new StringType(),
					new IntegerType(),
				]),
				new StringType(),
				CommonUnionType::class,
				'int|string',
			],
			[
				new UnionIterableType(
					new IntegerType(),
					[
						new ObjectType('ArrayObject'),
					]
				),
				new StringType(),
				UnionIterableType::class,
				'int[]|ArrayObject|string',
			],
			[
				new UnionIterableType(
					new IntegerType(),
					[
						new ObjectType('ArrayObject'),
					]
				),
				new ArrayType(new StringType()),
				CommonUnionType::class,
				'ArrayObject|int[]|string[]',
			],
			[
				new CommonUnionType([
					new TrueBooleanType(),
					new IntegerType(),
				]),
				new ArrayType(new StringType()),
				UnionIterableType::class,
				'string[]|int|true',
			],
			[
				new CommonUnionType([
					new ArrayType(new ObjectType('Foo')),
					new ArrayType(new ObjectType('Bar')),
				]),
				new ArrayType(new MixedType()),
				CommonUnionType::class,
				'Bar[]|Foo[]|mixed[]',
			],
		];
	}

	/**
	 * @dataProvider dataCombine
	 * @param \PHPStan\Type\Type $firstType
	 * @param \PHPStan\Type\Type $secondType
	 * @param string $expectedTypeClass
	 * @param string $expectedTypeDescription
	 */
	public function testCombine(
		Type $firstType,
		Type $secondType,
		string $expectedTypeClass,
		string $expectedTypeDescription
	)
	{
		$result = TypeCombinator::combine($firstType, $secondType);
		$this->assertInstanceOf($expectedTypeClass, $result);
		$this->assertSame($expectedTypeDescription, $result->describe());
	}

	/**
	 * @dataProvider dataCombine
	 * @param \PHPStan\Type\Type $firstType
	 * @param \PHPStan\Type\Type $secondType
	 * @param string $expectedTypeClass
	 * @param string $expectedTypeDescription
	 */
	public function testCombineInversed(
		Type $firstType,
		Type $secondType,
		string $expectedTypeClass,
		string $expectedTypeDescription
	)
	{
		$result = TypeCombinator::combine($secondType, $firstType);
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
				UnionIterableType::class,
				'string[]|ArrayObject',
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
		$this->assertInstanceOf($expectedTypeClass, $result);
		$this->assertSame($expectedTypeDescription, $result->describe());
	}

}
