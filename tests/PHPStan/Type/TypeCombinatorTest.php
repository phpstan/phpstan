<?php declare(strict_types = 1);

namespace PHPStan\Type;

class TypeCombinatorTest extends \PHPStan\TestCase
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
					new ObjectType('Doctrine\Common\Collections\Collection'),
				]),
				UnionIterableType::class,
				'string[]|Doctrine\Common\Collections\Collection|null',
			],
			[
				new UnionIterableType(new StringType(), [
					new ObjectType('Doctrine\Common\Collections\Collection'),
					new NullType(),
				]),
				UnionIterableType::class,
				'string[]|Doctrine\Common\Collections\Collection|null',
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
					new ObjectType('Doctrine\Common\Collections\Collection'),
					new NullType(),
				]),
				UnionIterableType::class,
				'string[]|Doctrine\Common\Collections\Collection',
			],
			[
				new UnionIterableType(new StringType(), [
					new ObjectType('Doctrine\Common\Collections\Collection'),
				]),
				UnionIterableType::class,
				'string[]|Doctrine\Common\Collections\Collection',
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
						new ObjectType('Doctrine\Common\Collections\Collection'),
					]
				),
				new StringType(),
				UnionIterableType::class,
				'int[]|Doctrine\Common\Collections\Collection|string',
			],
			[
				new UnionIterableType(
					new IntegerType(),
					[
						new ObjectType('Doctrine\Common\Collections\Collection'),
					]
				),
				new ArrayType(new StringType()),
				CommonUnionType::class,
				'Doctrine\Common\Collections\Collection|int[]|string[]',
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
