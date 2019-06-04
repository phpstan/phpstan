<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Reflection\Native\NativeParameterReflection;
use PHPStan\Reflection\PassedByReference;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Accessory\HasMethodType;
use PHPStan\Type\Generic\TemplateMixedType;

class CallableTypeTest extends \PHPStan\Testing\TestCase
{

	public function dataIsSuperTypeOf(): array
	{
		return [
			[
				new CallableType(),
				new ClosureType([], new MixedType(), false),
				TrinaryLogic::createYes(),
			],
			[
				new CallableType(),
				new HasMethodType('format'),
				TrinaryLogic::createMaybe(),
			],
			[
				new CallableType(),
				new HasMethodType('__invoke'),
				TrinaryLogic::createYes(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSuperTypeOf
	 * @param CallableType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSuperTypeOf(CallableType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isSuperTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}

	public function dataIsSubTypeOf(): array
	{
		return [
			[
				new CallableType(),
				new CallableType(),
				TrinaryLogic::createYes(),
			],
			[
				new CallableType(),
				new StringType(),
				TrinaryLogic::createMaybe(),
			],
			[
				new CallableType(),
				new IntegerType(),
				TrinaryLogic::createNo(),
			],
			[
				new CallableType(),
				new UnionType([new CallableType(), new NullType()]),
				TrinaryLogic::createYes(),
			],
			[
				new CallableType(),
				new UnionType([new StringType(), new NullType()]),
				TrinaryLogic::createMaybe(),
			],
			[
				new CallableType(),
				new UnionType([new IntegerType(), new NullType()]),
				TrinaryLogic::createNo(),
			],
			[
				new CallableType(),
				new IntersectionType([new CallableType()]),
				TrinaryLogic::createYes(),
			],
			[
				new CallableType(),
				new IntersectionType([new StringType()]),
				TrinaryLogic::createMaybe(),
			],
			[
				new CallableType(),
				new IntersectionType([new IntegerType()]),
				TrinaryLogic::createNo(),
			],
			[
				new CallableType(),
				new IntersectionType([new CallableType(), new StringType()]),
				TrinaryLogic::createMaybe(),
			],
			[
				new CallableType(),
				new IntersectionType([new CallableType(), new ObjectType('Unknown')]),
				TrinaryLogic::createMaybe(),
			],
			[
				new CallableType(),
				new HasMethodType('foo'),
				TrinaryLogic::createMaybe(),
			],
			[
				new CallableType(),
				new HasMethodType('__invoke'),
				TrinaryLogic::createMaybe(),
			],
		];
	}

	/**
	 * @dataProvider dataIsSubTypeOf
	 * @param CallableType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSubTypeOf(CallableType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $type->isSubTypeOf($otherType);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSubTypeOf(%s)', $type->describe(VerbosityLevel::precise()), $otherType->describe(VerbosityLevel::precise()))
		);
	}

	/**
	 * @dataProvider dataIsSubTypeOf
	 * @param CallableType $type
	 * @param Type $otherType
	 * @param TrinaryLogic $expectedResult
	 */
	public function testIsSubTypeOfInversed(CallableType $type, Type $otherType, TrinaryLogic $expectedResult): void
	{
		$actualResult = $otherType->isSuperTypeOf($type);
		$this->assertSame(
			$expectedResult->describe(),
			$actualResult->describe(),
			sprintf('%s -> isSuperTypeOf(%s)', $otherType->describe(VerbosityLevel::precise()), $type->describe(VerbosityLevel::precise()))
		);
	}

	public function dataInferTemplateTypes(): array
	{
		$param = static function (Type $type): NativeParameterReflection {
			return new NativeParameterReflection(
				'',
				false,
				$type,
				PassedByReference::createNo(),
				false
			);
		};

		return [
			'template param' => [
				new CallableType(
					[
						$param(new StringType()),
					],
					new IntegerType()
				),
				new CallableType(
					[
						$param(new TemplateMixedType('T')),
					],
					new IntegerType()
				),
				['T' => 'string'],
			],
			'template return' => [
				new CallableType(
					[
						$param(new StringType()),
					],
					new IntegerType()
				),
				new CallableType(
					[
						$param(new StringType()),
					],
					new TemplateMixedType('T')
				),
				['T' => 'int'],
			],
			'multiple templates' => [
				new CallableType(
					[
						$param(new StringType()),
						$param(new ObjectType('DateTime')),
					],
					new IntegerType()
				),
				new CallableType(
					[
						$param(new StringType()),
						$param(new TemplateMixedType('A')),
					],
					new TemplateMixedType('B')
				),
				['A' => 'DateTime', 'B' => 'int'],
			],
			'receive union' => [
				new UnionType([
					new NullType(),
					new CallableType(
						[
							$param(new StringType()),
							$param(new ObjectType('DateTime')),
						],
						new IntegerType()
					),
				]),
				new CallableType(
					[
						$param(new StringType()),
						$param(new TemplateMixedType('A')),
					],
					new TemplateMixedType('B')
				),
				['A' => 'DateTime', 'B' => 'int'],
			],
			'receive non-accepted' => [
				new NullType(),
				new CallableType(
					[
						$param(new StringType()),
						$param(new TemplateMixedType('A')),
					],
					new TemplateMixedType('B')
				),
				[],
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
