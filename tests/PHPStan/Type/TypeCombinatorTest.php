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
				new StringType(false),
				CommonUnionType::class,
				'string|null',
			],
			[
				new CommonUnionType([
					new StringType(false),
					new IntegerType(false),
				], false),
				CommonUnionType::class,
				'string|int|null',
			],
			[
				new CommonUnionType([
					new StringType(false),
					new IntegerType(false),
					new NullType(),
				], false),
				CommonUnionType::class,
				'string|int|null',
			],
			[
				new UnionIterableType(new StringType(false), false, [
					new ObjectType('Doctrine\Common\Collections\Collection', false),
				]),
				UnionIterableType::class,
				'string[]|Doctrine\Common\Collections\Collection|null',
			],
			[
				new UnionIterableType(new StringType(false), false, [
					new ObjectType('Doctrine\Common\Collections\Collection', false),
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
				NullType::class,
				'null',
			],
			[
				new VoidType(),
				VoidType::class,
				'void',
			],
			[
				new StringType(false),
				StringType::class,
				'string',
			],
			[
				new CommonUnionType([
					new StringType(false),
					new IntegerType(false),
					new NullType(),
				], false),
				CommonUnionType::class,
				'string|int',
			],
			[
				new CommonUnionType([
					new StringType(false),
					new IntegerType(false),
				], false),
				CommonUnionType::class,
				'string|int',
			],
			[
				new UnionIterableType(new StringType(false), false, [
					new ObjectType('Doctrine\Common\Collections\Collection', false),
					new NullType(),
				]),
				UnionIterableType::class,
				'string[]|Doctrine\Common\Collections\Collection',
			],
			[
				new UnionIterableType(new StringType(false), false, [
					new ObjectType('Doctrine\Common\Collections\Collection', false),
				]),
				UnionIterableType::class,
				'string[]|Doctrine\Common\Collections\Collection',
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

}
