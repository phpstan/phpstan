<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PhpParser\Node\Name;
use PHPStan\Reflection\Native\NativeParameterReflection;
use PHPStan\Reflection\Php\DummyParameter;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\FloatType;
use PHPStan\Type\Generic\TemplateTypeFactory;
use PHPStan\Type\Generic\TemplateTypeScope;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;

class ParametersAcceptorSelectorTest extends \PHPStan\Testing\TestCase
{

	public function dataSelectFromTypes(): \Generator
	{
		require_once __DIR__ . '/data/function-definitions.php';
		$broker = $this->createBroker();

		$arrayRandVariants = $broker->getFunction(new Name('array_rand'), null)->getVariants();
		yield [
			[
				new ArrayType(new MixedType(), new MixedType()),
				new IntegerType(),
			],
			$arrayRandVariants,
			false,
			$arrayRandVariants[0],
		];

		yield [
			[
				new ArrayType(new MixedType(), new MixedType()),
			],
			$arrayRandVariants,
			false,
			$arrayRandVariants[1],
		];

		$datePeriodConstructorVariants = $broker->getClass('DatePeriod')->getNativeMethod('__construct')->getVariants();
		yield [
			[
				new ObjectType(\DateTimeInterface::class),
				new ObjectType(\DateInterval::class),
				new IntegerType(),
				new IntegerType(),
			],
			$datePeriodConstructorVariants,
			false,
			$datePeriodConstructorVariants[0],
		];
		yield [
			[
				new ObjectType(\DateTimeInterface::class),
				new ObjectType(\DateInterval::class),
				new ObjectType(\DateTimeInterface::class),
				new IntegerType(),
			],
			$datePeriodConstructorVariants,
			false,
			$datePeriodConstructorVariants[1],
		];
		yield [
			[
				new StringType(),
				new IntegerType(),
			],
			$datePeriodConstructorVariants,
			false,
			$datePeriodConstructorVariants[2],
		];

		$ibaseWaitEventVariants = $broker->getFunction(new Name('ibase_wait_event'), null)->getVariants();
		yield [
			[
				new ResourceType(),
			],
			$ibaseWaitEventVariants,
			false,
			$ibaseWaitEventVariants[0],
		];
		yield [
			[
				new StringType(),
			],
			$ibaseWaitEventVariants,
			false,
			$ibaseWaitEventVariants[1],
		];
		yield [
			[
				new StringType(),
				new StringType(),
				new StringType(),
				new StringType(),
				new StringType(),
			],
			$ibaseWaitEventVariants,
			false,
			new FunctionVariant(
				[
					new NativeParameterReflection(
						'link_identifier|event',
						false,
						new MixedType(),
						PassedByReference::createNo(),
						false,
						null
					),
					new NativeParameterReflection(
						'event|args',
						true,
						new UnionType([
							new ArrayType(new IntegerType(), new MixedType()),
							new StringType(),
						]),
						PassedByReference::createNo(),
						true,
						null
					),
				],
				true,
				new StringType()
			),
		];

		$absVariants = $broker->getFunction(new Name('abs'), null)->getVariants();
		yield [
			[
				new FloatType(),
				new FloatType(),
			],
			$absVariants,
			false,
			ParametersAcceptorSelector::combineAcceptors($absVariants),
		];
		yield [
			[
				new StringType(),
			],
			$absVariants,
			false,
			ParametersAcceptorSelector::combineAcceptors($absVariants),
		];

		$strtokVariants = $broker->getFunction(new Name('strtok'), null)->getVariants();
		yield [
			[],
			$strtokVariants,
			false,
			new FunctionVariant(
				[
					new NativeParameterReflection(
						'str|token',
						false,
						new StringType(),
						PassedByReference::createNo(),
						false,
						null
					),
					new NativeParameterReflection(
						'token',
						true,
						new StringType(),
						PassedByReference::createNo(),
						false,
						null
					),
				],
				false,
				new UnionType([new StringType(), new ConstantBooleanType(false)])
			),
		];
		yield [
			[
				new StringType(),
			],
			$strtokVariants,
			true,
			ParametersAcceptorSelector::combineAcceptors($strtokVariants),
		];

		$variadicVariants = [
			new FunctionVariant(
				[
					new NativeParameterReflection(
						'int',
						false,
						new IntegerType(),
						PassedByReference::createNo(),
						false,
						null
					),
					new NativeParameterReflection(
						'intVariadic',
						true,
						new IntegerType(),
						PassedByReference::createNo(),
						true,
						null
					),
				],
				true,
				new IntegerType()
			),
			new FunctionVariant(
				[
					new NativeParameterReflection(
						'int',
						false,
						new IntegerType(),
						PassedByReference::createNo(),
						false,
						null
					),
					new NativeParameterReflection(
						'floatVariadic',
						true,
						new FloatType(),
						PassedByReference::createNo(),
						true,
						null
					),
				],
				true,
				new IntegerType()
			),
		];

		yield [
			[
				new IntegerType(),
			],
			$variadicVariants,
			true,
			ParametersAcceptorSelector::combineAcceptors($variadicVariants),
		];

		$defaultValuesVariants1 = [
			new FunctionVariant(
				[
					new DummyParameter(
						'a',
						new MixedType(),
						false,
						PassedByReference::createNo(),
						false,
						new ConstantIntegerType(1)
					),
				],
				false,
				new NullType()
			),
			new FunctionVariant(
				[
					new DummyParameter(
						'a',
						new MixedType(),
						false,
						PassedByReference::createNo(),
						false,
						new ConstantIntegerType(2)
					),
				],
				false,
				new NullType()
			),
		];

		yield [
			[
				new IntegerType(),
			],
			$defaultValuesVariants1,
			true,
			new FunctionVariant(
				[
					new DummyParameter(
						'a',
						new MixedType(),
						false,
						PassedByReference::createNo(),
						false,
						new UnionType([
							new ConstantIntegerType(1),
							new ConstantIntegerType(2),
						])
					),
				],
				false,
				new NullType()
			),
		];

		$defaultValuesVariants2 = [
			new FunctionVariant(
				[
					new DummyParameter(
						'a',
						new MixedType(),
						false,
						PassedByReference::createNo(),
						false,
						new ConstantIntegerType(1)
					),
				],
				false,
				new NullType()
			),
			new FunctionVariant(
				[
					new DummyParameter(
						'a',
						new MixedType(),
						false,
						PassedByReference::createNo(),
						false,
						null
					),
				],
				false,
				new NullType()
			),
		];

		yield [
			[
				new IntegerType(),
			],
			$defaultValuesVariants2,
			true,
			new FunctionVariant(
				[
					new DummyParameter(
						'a',
						new MixedType(),
						false,
						PassedByReference::createNo(),
						false,
						null
					),
				],
				false,
				new NullType()
			),
		];

		$genericVariants = [
			new FunctionVariant(
				[
					new DummyParameter(
						'a',
						TemplateTypeFactory::create(
							new TemplateTypeScope(null, null),
							'T',
							null
						),
						false,
						PassedByReference::createNo(),
						false,
						null
					),
				],
				false,
				new NullType()
			),
		];

		yield [
			[
				new IntegerType(),
			],
			$genericVariants,
			true,
			new FunctionVariant(
				[
					new DummyParameter(
						'a',
						new IntegerType(),
						false,
						PassedByReference::createNo(),
						false,
						null
					),
				],
				false,
				new NullType()
			),
		];
	}

	/**
	 * @dataProvider dataSelectFromTypes
	 * @param \PHPStan\Type\Type[] $types
	 * @param ParametersAcceptor[] $variants
	 * @param bool $unpack
	 * @param ParametersAcceptor $expected
	 */
	public function testSelectFromTypes(
		array $types,
		array $variants,
		bool $unpack,
		ParametersAcceptor $expected
	): void
	{
		$selectedAcceptor = ParametersAcceptorSelector::selectFromTypes($types, $variants, $unpack);
		$this->assertCount(count($expected->getParameters()), $selectedAcceptor->getParameters());
		foreach ($selectedAcceptor->getParameters() as $i => $parameter) {
			$expectedParameter = $expected->getParameters()[$i];
			$this->assertSame(
				$expectedParameter->getName(),
				$parameter->getName()
			);
			$this->assertSame(
				$expectedParameter->isOptional(),
				$parameter->isOptional()
			);
			$this->assertSame(
				$expectedParameter->getType()->describe(VerbosityLevel::precise()),
				$parameter->getType()->describe(VerbosityLevel::precise())
			);
			$this->assertTrue(
				$expectedParameter->passedByReference()->equals($parameter->passedByReference())
			);
			$this->assertSame(
				$expectedParameter->isVariadic(),
				$parameter->isVariadic()
			);
			if ($expectedParameter->getDefaultValue() === null) {
				$this->assertNull($parameter->getDefaultValue());
			} else {
				$this->assertSame(
					$expectedParameter->getDefaultValue()->describe(VerbosityLevel::precise()),
					$parameter->getDefaultValue() !== null ? $parameter->getDefaultValue()->describe(VerbosityLevel::precise()) : null
				);
			}
		}

		$this->assertSame(
			$expected->getReturnType()->describe(VerbosityLevel::precise()),
			$selectedAcceptor->getReturnType()->describe(VerbosityLevel::precise())
		);
		$this->assertSame($expected->isVariadic(), $selectedAcceptor->isVariadic());
	}

}
