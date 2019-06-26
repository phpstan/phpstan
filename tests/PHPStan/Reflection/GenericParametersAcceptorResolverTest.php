<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Reflection\Php\DummyParameter;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Generic\TemplateTypeFactory;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\Generic\TemplateTypeScope;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

class GenericParametersAcceptorResolverTest  extends \PHPStan\Testing\TestCase
{

	/**
	 * @return array<array{Type[], ParametersAcceptor, ParametersAcceptor}>
	 */
	public function dataResolve(): array
	{
		$templateType = static function (string $name, ?Type $type = null): Type {
			return TemplateTypeFactory::create(
				TemplateTypeScope::createWithFunction('a'),
				$name,
				$type
			);
		};

		return [
			'one param, one arg' => [
				[
					new ObjectType('DateTime'),
				],
				new FunctionVariant(
					TemplateTypeMap::createEmpty(),
					[
						new DummyParameter(
							'a',
							$templateType('T'),
							false,
							PassedByReference::createNo(),
							false,
							null
						),
					],
					false,
					new NullType()
				),
				new FunctionVariant(
					TemplateTypeMap::createEmpty(),
					[
						new DummyParameter(
							'a',
							new ObjectType('DateTime'),
							false,
							PassedByReference::createNo(),
							false,
							null
						),
					],
					false,
					new NullType()
				),
			],
			'two params, two args, return type' => [
				[
					new ObjectType('DateTime'),
					new IntegerType(),
				],
				new FunctionVariant(
					TemplateTypeMap::createEmpty(),
					[
						new DummyParameter(
							'a',
							$templateType('T'),
							false,
							PassedByReference::createNo(),
							false,
							null
						),
						new DummyParameter(
							'b',
							$templateType('U'),
							false,
							PassedByReference::createNo(),
							false,
							null
						),
					],
					false,
					$templateType('U')
				),
				new FunctionVariant(
					TemplateTypeMap::createEmpty(),
					[
						new DummyParameter(
							'a',
							new ObjectType('DateTime'),
							false,
							PassedByReference::createNo(),
							false,
							null
						),
						new DummyParameter(
							'b',
							new IntegerType(),
							false,
							PassedByReference::createNo(),
							false,
							null
						),
					],
					false,
					new IntegerType()
				),
			],
			'mixed types' => [
				[
					new ObjectType('DateTime'),
					new IntegerType(),
				],
				new FunctionVariant(
					TemplateTypeMap::createEmpty(),
					[
						new DummyParameter(
							'a',
							$templateType('T'),
							false,
							PassedByReference::createNo(),
							false,
							null
						),
						new DummyParameter(
							'b',
							$templateType('T'),
							false,
							PassedByReference::createNo(),
							false,
							null
						),
					],
					false,
					$templateType('T')
				),
				new FunctionVariant(
					TemplateTypeMap::createEmpty(),
					[
						new DummyParameter(
							'a',
							new UnionType([
								new ObjectType('DateTime'),
								new IntegerType(),
							]),
							false,
							PassedByReference::createNo(),
							false,
							null
						),
						new DummyParameter(
							'b',
							new UnionType([
								new ObjectType('DateTime'),
								new IntegerType(),
							]),
							false,
							PassedByReference::createNo(),
							false,
							null
						),
					],
					false,
					new UnionType([
						new ObjectType('DateTime'),
						new IntegerType(),
					])
				),
			],
			'parameter default value' => [
				[
					new ObjectType('DateTime'),
				],
				new FunctionVariant(
					TemplateTypeMap::createEmpty(),
					[
						new DummyParameter(
							'a',
							$templateType('T'),
							false,
							PassedByReference::createNo(),
							false,
							null
						),
						new DummyParameter(
							'b',
							$templateType('U'),
							true,
							PassedByReference::createNo(),
							false,
							new ConstantIntegerType(42)
						),
					],
					false,
					new NullType()
				),
				new FunctionVariant(
					TemplateTypeMap::createEmpty(),
					[
						new DummyParameter(
							'a',
							new ObjectType('DateTime'),
							false,
							PassedByReference::createNo(),
							false,
							null
						),
						new DummyParameter(
							'b',
							new ConstantIntegerType(42),
							false,
							PassedByReference::createNo(),
							false,
							null
						),
					],
					false,
					new NullType()
				),
			],
			'variadic parameter' => [
				[
					new ObjectType('DateTime'),
					new ConstantIntegerType(1),
					new ConstantIntegerType(2),
					new ConstantIntegerType(3),
				],
				new FunctionVariant(
					TemplateTypeMap::createEmpty(),
					[
						new DummyParameter(
							'a',
							$templateType('T'),
							false,
							PassedByReference::createNo(),
							false,
							null
						),
						new DummyParameter(
							'b',
							new ArrayType(new MixedType(), $templateType('U')),
							false,
							PassedByReference::createNo(),
							true,
							null
						),
					],
					true,
					$templateType('U')
				),
				new FunctionVariant(
					TemplateTypeMap::createEmpty(),
					[
						new DummyParameter(
							'a',
							new ObjectType('DateTime'),
							false,
							PassedByReference::createNo(),
							false,
							null
						),
						new DummyParameter(
							'b',
							new ArrayType(
								new MixedType(),
								new UnionType([
									new ConstantIntegerType(1),
									new ConstantIntegerType(2),
									new ConstantIntegerType(3),
								])
							),
							false,
							PassedByReference::createNo(),
							false,
							null
						),
					],
					false,
					new UnionType([
						new ConstantIntegerType(1),
						new ConstantIntegerType(2),
						new ConstantIntegerType(3),
					])
				),
			],
			'missing args' => [
				[
					new ObjectType('DateTime'),
				],
				new FunctionVariant(
					TemplateTypeMap::createEmpty(),
					[
						new DummyParameter(
							'a',
							$templateType('T'),
							false,
							PassedByReference::createNo(),
							false,
							null
						),
						new DummyParameter(
							'b',
							$templateType('U'),
							false,
							PassedByReference::createNo(),
							false,
							null
						),
					],
					false,
					new NullType()
				),
				new FunctionVariant(
					TemplateTypeMap::createEmpty(),
					[
						new DummyParameter(
							'a',
							new ObjectType('DateTime'),
							false,
							PassedByReference::createNo(),
							false,
							null
						),
						new DummyParameter(
							'b',
							new ErrorType(),
							false,
							PassedByReference::createNo(),
							false,
							null
						),
					],
					false,
					new NullType()
				),
			],
			'constant string arg resolved to constant string' => [
				[
					new ConstantStringType('foo'),
				],
				new FunctionVariant(
					TemplateTypeMap::createEmpty(),
					[
						new DummyParameter('str', $templateType('T'), false, null, false, null),
					],
					false,
					$templateType('T')
				),
				new FunctionVariant(
					TemplateTypeMap::createEmpty(),
					[
						new DummyParameter('str', new ConstantStringType('foo'), false, null, false, null),
					],
					false,
					new ConstantStringType('foo')
				),
			],
		];
	}

	/**
	 * @dataProvider dataResolve
	 */
	public function testResolve(array $argTypes, ParametersAcceptor $parametersAcceptor, ParametersAcceptor $expectedResult): void
	{
		$result = GenericParametersAcceptorResolver::resolve(
			$argTypes,
			$parametersAcceptor
		);

		$this->assertInstanceOf(
			get_class($expectedResult->getReturnType()),
			$result->getReturnType(),
			'Unexpected return type'
		);
		$this->assertSame(
			$expectedResult->getReturnType()->describe(VerbosityLevel::precise()),
			$result->getReturnType()->describe(VerbosityLevel::precise()),
			'Unexpected return type'
		);

		$resultParameters = $result->getParameters();
		$expectedParameters = $expectedResult->getParameters();

		$this->assertCount(count($expectedParameters), $resultParameters);

		foreach ($expectedParameters as $i => $param) {
			$this->assertInstanceOf(
				get_class($param->getType()),
				$resultParameters[$i]->getType(),
				sprintf('Unexpected parameter %d', $i + 1)
			);
			$this->assertSame(
				$param->getType()->describe(VerbosityLevel::precise()),
				$resultParameters[$i]->getType()->describe(VerbosityLevel::precise()),
				sprintf('Unexpected parameter %d', $i + 1)
			);
		}
	}

}
