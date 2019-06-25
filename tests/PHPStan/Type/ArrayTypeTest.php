<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Generic\TemplateTypeFactory;
use PHPStan\Type\Generic\TemplateTypeScope;

class ArrayTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataIsSuperTypeOf(): array
	{
		return [
			[
				new ArrayType(new MixedType(), new StringType()),
				new ArrayType(new MixedType(), new StringType()),
				TrinaryLogic::createYes(),
			],
			[
				new ArrayType(new MixedType(), new StringType()),
				new ArrayType(new MixedType(), new IntegerType()),
				TrinaryLogic::createNo(),
			],
			[
				new ArrayType(new MixedType(), new StringType()),
				new ArrayType(new IntegerType(), new StringType()),
				TrinaryLogic::createYes(),
			],
			[
				new ArrayType(new IntegerType(), new StringType()),
				new ArrayType(new MixedType(), new StringType()),
				TrinaryLogic::createMaybe(),
			],
			[
				new ArrayType(new IntegerType(), new StringType()),
				new ArrayType(new StringType(), new StringType()),
				TrinaryLogic::createNo(),
			],
			[
				new ArrayType(new MixedType(), new MixedType()),
				new CallableType(),
				TrinaryLogic::createMaybe(),
			],
			[
				new ArrayType(new IntegerType(), new StringType()),
				new ConstantArrayType([], []),
				TrinaryLogic::createYes(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 * @param ArrayType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSuperTypeOf(ArrayType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}

	public function dataAccepts(): array
	{
		return [
			[
				new ArrayType(new MixedType(), new StringType()),
				new UnionType([
					new ConstantArrayType([], []),
					new ConstantArrayType(
						[new ConstantIntegerType(0)],
						[new MixedType()]
					),
					new ConstantArrayType([
						new ConstantIntegerType(0),
						new ConstantIntegerType(1),
					], [
						new StringType(),
						new MixedType(),
					]),
				]),
				TrinaryLogic::createYes(),
			],
		];
	}

	/**
	 * @dataProvider dataAccepts
	 * @param ArrayType $acceptingType
	 * @param Type $acceptedType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testAccepts(
		ArrayType $acceptingType,
		Type $acceptedType,
		TrinaryLogic $expectedResult
	): void
	{
		$actualResult = $acceptingType->accepts($acceptedType, true);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> accepts(%s)', $acceptingType->describe(VerbosityLevel::precise()), $acceptedType->describe(VerbosityLevel::precise()))
		);
	}

	public function dataDescribe(): array
	{
		return [
			[
				new ArrayType(new BenevolentUnionType([
					new IntegerType(),
					new StringType(),
				]), new IntegerType()),
				'array<int>',
			],
		];
	}

	/**
	 * @dataProvider dataDescribe
	 * @param ArrayType $type
	 * @param string $expectedDescription
	 */
	public function testDescribe(
		ArrayType $type,
		string $expectedDescription
	): void
	{
		$this->assertSame($expectedDescription, $type->describe(VerbosityLevel::precise()));
	}

	public function dataInferTemplateTypes(): array
	{
		$templateType = static function (string $name): Type {
			return TemplateTypeFactory::create(
				TemplateTypeScope::createWithFunction('a'),
				$name,
				new MixedType()
			);
		};

		return [
			'valid templated item' => [
				new ArrayType(
					new MixedType(),
					new ObjectType('DateTime')
				),
				new ArrayType(
					new MixedType(),
					$templateType('T')
				),
				['T' => 'DateTime'],
			],
			'receive mixed' => [
				new MixedType(),
				new ArrayType(
					new MixedType(),
					$templateType('T')
				),
				[],
			],
			'receive non-accepted' => [
				new StringType(),
				new ArrayType(
					new MixedType(),
					$templateType('T')
				),
				[],
			],
			'receive union items' => [
				new ArrayType(
					new MixedType(),
					new UnionType([
						new StringType(),
						new IntegerType(),
					])
				),
				new ArrayType(
					new MixedType(),
					$templateType('T')
				),
				['T' => 'int|string'],
			],
			'receive union' => [
				new UnionType([
					new StringType(),
					new ArrayType(
						new MixedType(),
						new StringType()
					),
					new ArrayType(
						new MixedType(),
						new IntegerType()
					),
				]),
				new ArrayType(
					new MixedType(),
					$templateType('T')
				),
				['T' => 'int|string'],
			],
		];
	}

	/**
	 * @dataProvider dataInferTemplateTypes
	 * @param array<string,string> $expectedTypes
	 */
	public function testResolveTemplateTypes(Type $received, Type $template, array $expectedTypes): void
	{
		$result = $template->inferTemplateTypes($received);

		$this->assertSame(
			$expectedTypes,
			array_map(static function (Type $type): string {
				return $type->describe(VerbosityLevel::precise());
			}, $result->getTypes())
		);
	}

}
