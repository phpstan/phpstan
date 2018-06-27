<?php declare(strict_types = 1);

namespace PHPStan\Reflection\SignatureMap;

use PHPStan\Reflection\PassedByReference;
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
use PHPStan\Type\VerbosityLevel;

class SignatureMapParserTest extends \PHPStan\Testing\TestCase
{

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
                            PassedByReference::createNo(),
                            false
                        ),
                        new ParameterSignature(
                            'fields',
                            false,
                            new ArrayType(new MixedType(), new MixedType()),
                            PassedByReference::createNo(),
                            false
                        ),
                        new ParameterSignature(
                            'delimiter',
                            true,
                            new StringType(),
                            PassedByReference::createNo(),
                            false
                        ),
                        new ParameterSignature(
                            'enclosure',
                            true,
                            new StringType(),
                            PassedByReference::createNo(),
                            false
                        ),
                        new ParameterSignature(
                            'escape_char',
                            true,
                            new StringType(),
                            PassedByReference::createNo(),
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
                            PassedByReference::createNo(),
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
                            PassedByReference::createReadsArgument(),
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
                            PassedByReference::createNo(),
                            false
                        ),
                        new ParameterSignature(
                            'out',
                            false,
                            new StringType(),
                            PassedByReference::createCreatesNewVariable(),
                            false
                        ),
                        new ParameterSignature(
                            'notext',
                            true,
                            new BooleanType(),
                            PassedByReference::createNo(),
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
                            PassedByReference::createNo(),
                            false
                        ),
                        new ParameterSignature(
                            'arr2',
                            false,
                            new ArrayType(new MixedType(), new MixedType()),
                            PassedByReference::createNo(),
                            false
                        ),
                        new ParameterSignature(
                            '...',
                            true,
                            new ArrayType(new MixedType(), new MixedType()),
                            PassedByReference::createNo(),
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
                            PassedByReference::createNo(),
                            false
                        ),
                        new ParameterSignature(
                            'event',
                            false,
                            new StringType(),
                            PassedByReference::createNo(),
                            false
                        ),
                        new ParameterSignature(
                            '...',
                            true,
                            new MixedType(),
                            PassedByReference::createNo(),
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
                            PassedByReference::createNo(),
                            false
                        ),
                        new ParameterSignature(
                            'args',
                            true,
                            new MixedType(),
                            PassedByReference::createNo(),
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
                            PassedByReference::createNo(),
                            false
                        ),
                        new ParameterSignature(
                            'args',
                            true,
                            new MixedType(),
                            PassedByReference::createNo(),
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
                            PassedByReference::createNo(),
                            false
                        ),
                    ],
                    new StaticType(\DateTime::class),
                    false
                ),
            ],
            [
                ['bool', '&rw_string' => 'string', '&...rw_strings=' => 'string'],
                null,
                new FunctionSignature(
                    [
                        new ParameterSignature(
                            'string',
                            false,
                            new StringType(),
                            PassedByReference::createReadsArgument(),
                            false
                        ),
                        new ParameterSignature(
                            'strings',
                            true,
                            new StringType(),
                            PassedByReference::createReadsArgument(),
                            true
                        ),
                    ],
                    new BooleanType(),
                    true
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
    ): void {
        /** @var SignatureMapParser $parser */
        $parser = self::getContainer()->getByType(SignatureMapParser::class);
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
                $expectedParameterSignature->getType()->describe(VerbosityLevel::value()),
                $parameterSignature->getType()->describe(VerbosityLevel::value()),
                sprintf('Type of parameter $%s does not match.', $parameterSignature->getName())
            );
            $this->assertTrue(
                $expectedParameterSignature->passedByReference()->equals($parameterSignature->passedByReference()),
                sprintf('Passed-by-reference of parameter $%s does not match.', $parameterSignature->getName())
            );
            $this->assertSame(
                $expectedParameterSignature->isVariadic(),
                $parameterSignature->isVariadic(),
                sprintf('Variadicity of parameter $%s does not match.', $parameterSignature->getName())
            );
        }

        $this->assertSame(
            $expectedSignature->getReturnType()->describe(VerbosityLevel::value()),
            $functionSignature->getReturnType()->describe(VerbosityLevel::value()),
            'Return type does not match.'
        );
        $this->assertSame(
            $expectedSignature->isVariadic(),
            $functionSignature->isVariadic(),
            'Variadicity does not match.'
        );
    }

    public function testParseAll(): void
    {
        $parser = self::getContainer()->getByType(SignatureMapParser::class);
        $signatureMap = require __DIR__ . '/../../../../src/Reflection/SignatureMap/functionMap.php';

        $count = 0;
        foreach ($signatureMap as $functionName => $map) {
            $className = null;
            if (strpos($functionName, '::') !== false) {
                $parts = explode('::', $functionName);
                $className = $parts[0];
            }

            try {
                $parser->getFunctionSignature($map, $className);
                $count++;
            } catch (\PHPStan\PhpDocParser\Parser\ParserException $e) {
                $this->fail(sprintf('Could not parse %s.', $functionName));
            }
        }

        $this->assertSame(13457, $count);
    }
}
