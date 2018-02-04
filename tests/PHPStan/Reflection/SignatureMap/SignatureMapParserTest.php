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
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;

class SignatureMapParserTest extends \PHPStan\Testing\TestCase
{

	public function dataGetFunctions(): array
	{
		return [
			[
				[
					'fputcsv' => ['int', 'fp' => 'resource', 'fields' => 'array', 'delimiter=' => 'string', 'enclosure=' => 'string', 'escape_char=' => 'string'],
					'rewind' => ['bool', 'fp' => 'resource'],
				],
				[
					'fputcsv' => new FunctionSignature(
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
					'rewind' => new FunctionSignature(
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
			],
			[
				[
					'natcasesort' => ['bool', '&rw_array_arg' => 'array'],
				],
				[
					'natcasesort' => new FunctionSignature(
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
			],
			[
				[
					'openssl_csr_export' => ['bool', 'csr' => 'string|resource', '&w_out' => 'string', 'notext=' => 'bool'],
				],
				[
					'openssl_csr_export' => new FunctionSignature(
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
			],
			[
				[
					'APCIterator::getTotalCount' => ['int'],
				],
				[],
			],
			[
				[
					'testFunction' => ['?Throwable|?Foo'],
				],
				[
					'testFunction' => new FunctionSignature(
						[],
						new UnionType([
							new ObjectType(\Throwable::class),
							new ObjectType('Foo'),
							new NullType(),
						]),
						false
					),
				],
			],
			[
				[
					'testFunction' => [''],
				],
				[
					'testFunction' => new FunctionSignature(
						[],
						new MixedType(),
						false
					),
				],
			],
			[
				[
					'mt_rand\'1' => ['int'],
					'mt_rand\'2' => ['int'],
					'mt_rand\'11' => ['int'],
				],
				[],
			],
			[
				[
					'array_diff_key' => ['array', 'arr1' => 'array', 'arr2' => 'array', '...=' => 'array'],
				],
				[
					'array_diff_key' => new FunctionSignature(
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
			],
			[
				[
					'ibase_set_event_handler' => ['resource', 'callback' => 'callable', 'event' => 'string', '...' => ''],
				],
				[
					'ibase_set_event_handler' => new FunctionSignature(
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
			],
			[
				[
					'pack' => ['string', 'format' => 'string', '...args=' => ''],
					'pack2' => ['string', 'format' => 'string', '...args' => ''],
				],
				[
					'pack' => new FunctionSignature(
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
					'pack2' => new FunctionSignature(
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
			],
		];
	}

	/**
	 * @dataProvider dataGetFunctions
	 * @param mixed[] $map
	 * @param \PHPStan\Reflection\SignatureMap\FunctionSignature[] $expectedSignatures
	 */
	public function testGetFunctions(array $map, array $expectedSignatures): void
	{
		/** @var SignatureMapParser $parser */
		$parser = $this->getContainer()->getByType(SignatureMapParser::class);
		$signatures = $parser->getFunctions($map);
		$this->assertCount(
			count($expectedSignatures),
			$signatures,
			'Number of function signatures does not match.'
		);
		foreach ($signatures as $functionName => $functionSignature) {
			$expectedSignature = $expectedSignatures[$functionName];
			$this->assertCount(
				count($expectedSignature->getParameters()),
				$functionSignature->getParameters(),
				sprintf('Number of parameters of %s() does not match.', $functionName)
			);

			foreach ($functionSignature->getParameters() as $i => $parameterSignature) {
				$expectedParameterSignature = $expectedSignature->getParameters()[$i];
				$this->assertSame(
					$expectedParameterSignature->getName(),
					$parameterSignature->getName(),
					sprintf('Name of parameter #%d of function %s() does not atch.', $i, $functionName)
				);
				$this->assertSame(
					$expectedParameterSignature->isOptional(),
					$parameterSignature->isOptional(),
					sprintf('Optionality of parameter $%s of function %s() does not match.', $parameterSignature->getName(), $functionName)
				);
				$this->assertSame(
					$expectedParameterSignature->getType()->describe(),
					$parameterSignature->getType()->describe(),
					sprintf('Type of parameter $%s of function %s() does not match.', $parameterSignature->getName(), $functionName)
				);
				$this->assertSame(
					$expectedParameterSignature->isPassedByReference(),
					$parameterSignature->isPassedByReference(),
					sprintf('Passed-by-reference of parameter $%s of function %s() does not match.', $parameterSignature->getName(), $functionName)
				);
				$this->assertSame(
					$expectedParameterSignature->isVariadic(),
					$parameterSignature->isVariadic(),
					sprintf('Variadicity of parameter $%s of function %s() does not match.', $parameterSignature->getName(), $functionName)
				);
			}

			$this->assertSame(
				$expectedSignature->getReturnType()->describe(),
				$functionSignature->getReturnType()->describe(),
				sprintf('Return type of function %s() does not match.', $functionName)
			);
			$this->assertSame(
				$expectedSignature->isVariadic(),
				$functionSignature->isVariadic(),
				sprintf('Variadicity of function %s() does not match.', $functionName)
			);
		}
	}

}
