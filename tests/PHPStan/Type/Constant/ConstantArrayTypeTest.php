<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PHPStan\TrinaryLogic;
use PHPStan\Type\ArrayType;
use PHPStan\Type\CallableType;
use PHPStan\Type\Generic\TemplateTypeFactory;
use PHPStan\Type\Generic\TemplateTypeScope;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class ConstantArrayTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataAccepts(): iterable
	{
		yield [
			new ConstantArrayType([], []),
			new ConstantArrayType([], []),
			TrinaryLogic::createYes(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			TrinaryLogic::createYes(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ConstantArrayType([], []),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([], []),
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ConstantArrayType([new ConstantIntegerType(7)], [new ConstantIntegerType(2)]),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(7)]),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ArrayType(new IntegerType(), new IntegerType()),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ArrayType(new StringType(), new StringType()),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ArrayType(new MixedType(), new MixedType()),
			TrinaryLogic::createMaybe(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new IterableType(new MixedType(), new IntegerType()),
			TrinaryLogic::createMaybe(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ConstantArrayType([], []),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantStringType('foo')], [new CallableType()]),
			new ConstantArrayType([], []),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantStringType('foo')], [new StringType()]),
			new ConstantArrayType([new ConstantStringType('foo'), new ConstantStringType('bar')], [new StringType(), new StringType()]),
			TrinaryLogic::createYes(),
		];

		yield [
			new ConstantArrayType([new ConstantStringType('foo')], [new StringType()]),
			new ConstantArrayType([new ConstantStringType('bar')], [new StringType()]),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantStringType('foo')], [new StringType()]),
			new ConstantArrayType([new ConstantStringType('foo')], [new ConstantStringType('bar')]),
			TrinaryLogic::createYes(),
		];
	}

	/**
	 * @dataProvider dataAccepts
	 * @param ConstantArrayType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testAccepts(ConstantArrayType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->accepts($otherType, true);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> accepts(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}

	public function dataIsSuperTypeOf(): iterable
	{
		yield [
			new ConstantArrayType([], []),
			new ConstantArrayType([], []),
			TrinaryLogic::createYes(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			TrinaryLogic::createYes(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ConstantArrayType([], []),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([], []),
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ConstantArrayType([new ConstantIntegerType(7)], [new ConstantIntegerType(2)]),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(7)]),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ArrayType(new IntegerType(), new IntegerType()),
			TrinaryLogic::createMaybe(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ArrayType(new StringType(), new StringType()),
			TrinaryLogic::createNo(),
		];

		yield [
			new ConstantArrayType([new ConstantIntegerType(1)], [new ConstantIntegerType(2)]),
			new ArrayType(new MixedType(), new MixedType()),
			TrinaryLogic::createMaybe(),
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 * @param ConstantArrayType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSuperTypeOf(ConstantArrayType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
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
			'receive constant array' => [
				new ConstantArrayType(
					[
						new ConstantStringType('a'),
						new ConstantStringType('b'),
					],
					[
						new StringType(),
						new IntegerType(),
					]
				),
				new ConstantArrayType(
					[
						new ConstantStringType('a'),
						new ConstantStringType('b'),
					],
					[
						$templateType('T'),
						$templateType('U'),
					]
				),
				['T' => 'string', 'U' => 'int'],
			],
			'receive constant array int' => [
				new ConstantArrayType(
					[
						new ConstantIntegerType(0),
						new ConstantIntegerType(1),
					],
					[
						new StringType(),
						new IntegerType(),
					]
				),
				new ConstantArrayType(
					[
						new ConstantIntegerType(0),
						new ConstantIntegerType(1),
					],
					[
						$templateType('T'),
						$templateType('U'),
					]
				),
				['T' => 'string', 'U' => 'int'],
			],
			'receive incompatible constant array' => [
				new ConstantArrayType(
					[
						new ConstantStringType('c'),
					],
					[
						new StringType(),
					]
				),
				new ConstantArrayType(
					[
						new ConstantStringType('a'),
						new ConstantStringType('b'),
					],
					[
						$templateType('T'),
						$templateType('U'),
					]
				),
				[],
			],
			'receive mixed' => [
				new MixedType(),
				new ConstantArrayType(
					[
						new ConstantStringType('a'),
					],
					[
						$templateType('T'),
					]
				),
				[],
			],
			'receive array' => [
				new ArrayType(new MixedType(), new StringType()),
				new ConstantArrayType(
					[
						new ConstantStringType('a'),
					],
					[
						$templateType('T'),
					]
				),
				['T' => 'string'],
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
