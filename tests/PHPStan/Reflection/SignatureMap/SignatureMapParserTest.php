<?php declare(strict_types = 1);

namespace PHPStan\Reflection\SignatureMap;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ResourceType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;

class SignatureMapParserTest extends \PHPStan\Testing\TestCase
{

	protected function setUp(): void
	{
		parent::setUp();
		$this->createBroker();
	}

	public function dataGetFunctions(): array
	{
		return [
			[
				['int', 'fp' => 'resource', 'fields' => 'array', 'delimiter=' => 'string', 'enclosure=' => 'string', 'escape_char=' => 'string'],
				null,
				new FunctionSignature(
					[
						new ParameterSignature(
							'fp',
							false,
							new ResourceType(),
							false,
							false
						),
						new ParameterSignature(
							'fields',
							false,
							new ArrayType(new MixedType(), new MixedType()),
							false,
							false
						),
						new ParameterSignature(
							'delimiter',
							true,
							new StringType(),
							false,
							false
						),
						new ParameterSignature(
							'enclosure',
							true,
							new StringType(),
							false,
							false
						),
						new ParameterSignature(
							'escape_char',
							true,
							new StringType(),
							false,
							false
						),
					],
					new IntegerType(),
					false
				),
			],
			[
				['bool', 'fp' => 'resource'],
				null,
				new FunctionSignature(
					[
						new ParameterSignature(
							'fp',
							false,
							new ResourceType(),
							false,
							false
						),
					],
					new BooleanType(),
					false
				),
			],
			[
				['bool', '&rw_array_arg' => 'array'],
				null,
				new FunctionSignature(
					[
						new ParameterSignature(
							'array_arg',
							false,
							new ArrayType(new MixedType(), new MixedType()),
							true,
							false
						),
					],
					new BooleanType(),
					false
				),
			],
			[
				['bool', 'csr' => 'string|resource', '&w_out' => 'string', 'notext=' => 'bool'],
				null,
				new FunctionSignature(
					[
						new ParameterSignature(
							'csr',
							false,
							new UnionType([
								new StringType(),
								new ResourceType(),
							]),
							false,
							false
						),
						new ParameterSignature(
							'out',
							false,
							new StringType(),
							true,
							false
						),
						new ParameterSignature(
							'notext',
							true,
							new BooleanType(),
							false,
							false
						),
					],
					new BooleanType(),
					false
				),
			],
			[
				['?Throwable|?Foo'],
				null,
				new FunctionSignature(
					[],
					new UnionType([
						new ObjectType(\Throwable::class),
						new ObjectType('Foo'),
						new NullType(),
					]),
					false
				),
			],
			[
				[''],
				null,
				new FunctionSignature(
					[],
					new MixedType(),
					false
				),
			],
			[
				['array', 'arr1' => 'array', 'arr2' => 'array', '...=' => 'array'],
				null,
				new FunctionSignature(
					[
						new ParameterSignature(
							'arr1',
							false,
							new ArrayType(new MixedType(), new MixedType()),
							false,
							false
						),
						new ParameterSignature(
							'arr2',
							false,
							new ArrayType(new MixedType(), new MixedType()),
							false,
							false
						),
						new ParameterSignature(
							'...',
							true,
							new ArrayType(new MixedType(), new MixedType()),
							false,
							true
						),
					],
					new ArrayType(new MixedType(), new MixedType()),
					true
				),
			],
			[
				['resource', 'callback' => 'callable', 'event' => 'string', '...' => ''],
				null,
				new FunctionSignature(
					[
						new ParameterSignature(
							'callback',
							false,
							new CallableType(),
							false,
							false
						),
						new ParameterSignature(
							'event',
							false,
							new StringType(),
							false,
							false
						),
						new ParameterSignature(
							'...',
							false,
							new MixedType(),
							false,
							true
						),
					],
					new ResourceType(),
					true
				),
			],
			[
				['string', 'format' => 'string', '...args=' => ''],
				null,
				new FunctionSignature(
					[
						new ParameterSignature(
							'format',
							false,
							new StringType(),
							false,
							false
						),
						new ParameterSignature(
							'args',
							true,
							new MixedType(),
							false,
							true
						),
					],
					new StringType(),
					true
				),
			],
			[
				['string', 'format' => 'string', '...args' => ''],
				null,
				new FunctionSignature(
					[
						new ParameterSignature(
							'format',
							false,
							new StringType(),
							false,
							false
						),
						new ParameterSignature(
							'args',
							false,
							new MixedType(),
							false,
							true
						),
					],
					new StringType(),
					true
				),
			],
			[
				['array<int,ReflectionParameter>'],
				null,
				new FunctionSignature(
					[],
					new ArrayType(new IntegerType(), new ObjectType(\ReflectionParameter::class)),
					false
				),
			],
			[
				['static', 'interval' => 'DateInterval'],
				\DateTime::class,
				new FunctionSignature(
					[
						new ParameterSignature(
							'interval',
							false,
							new ObjectType(\DateInterval::class),
							false,
							false
						),
					],
					new StaticType(\DateTime::class),
					false
				),
			],
		];
	}

	/**
	 * @dataProvider dataGetFunctions
	 * @param mixed[] $map
	 * @param string|null $className
	 * @param \PHPStan\Reflection\SignatureMap\FunctionSignature $expectedSignature
	 */
	public function testGetFunctions(
		array $map,
		?string $className,
		FunctionSignature $expectedSignature
	): void
	{
		/** @var SignatureMapParser $parser */
		$parser = $this->getContainer()->getByType(SignatureMapParser::class);
		$functionSignature = $parser->getFunctionSignature($map, $className);
		$this->assertCount(
			count($expectedSignature->getParameters()),
			$functionSignature->getParameters(),
			'Number of parameters does not match.'
		);

		foreach ($functionSignature->getParameters() as $i => $parameterSignature) {
			$expectedParameterSignature = $expectedSignature->getParameters()[$i];
			$this->assertSame(
				$expectedParameterSignature->getName(),
				$parameterSignature->getName(),
				sprintf('Name of parameter #%d does not match.', $i)
			);
			$this->assertSame(
				$expectedParameterSignature->isOptional(),
				$parameterSignature->isOptional(),
				sprintf('Optionality of parameter $%s does not match.', $parameterSignature->getName())
			);
			$this->assertSame(
				$expectedParameterSignature->getType()->describe(),
				$parameterSignature->getType()->describe(),
				sprintf('Type of parameter $%s does not match.', $parameterSignature->getName())
			);
			$this->assertSame(
				$expectedParameterSignature->isPassedByReference(),
				$parameterSignature->isPassedByReference(),
				sprintf('Passed-by-reference of parameter $%s does not match.', $parameterSignature->getName())
			);
			$this->assertSame(
				$expectedParameterSignature->isVariadic(),
				$parameterSignature->isVariadic(),
				sprintf('Variadicity of parameter $%s does not match.', $parameterSignature->getName())
			);
		}

		$this->assertSame(
			$expectedSignature->getReturnType()->describe(),
			$functionSignature->getReturnType()->describe(),
			'Return type does not match.'
		);
		$this->assertSame(
			$expectedSignature->isVariadic(),
			$functionSignature->isVariadic(),
			'Variadicity does not match.'
		);
	}

}
