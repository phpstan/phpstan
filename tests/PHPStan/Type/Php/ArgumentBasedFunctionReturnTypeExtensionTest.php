<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;

class ArgumentBasedFunctionReturnTypeExtensionTest extends \PHPStan\Testing\TestCase
{

	/**
	 * @return mixed[]
	 */
	public function dataFunctions(): array
	{
		return [
			[
				'array_unique',
				[
					new Arg(new Variable('foo')),
				],
				'array<int, string>',
			],
		];
	}

	/**
	 * @dataProvider dataFunctions
	 * @param string $functionName
	 * @param mixed[] $functionArguments
	 * @param string $expectedDescription
	 */
	public function testFunctions(string $functionName, array $functionArguments, string $expectedDescription): void
	{
		$functionCall = new FuncCall(new Name($functionName), $functionArguments);
		$extension = new ArgumentBasedFunctionReturnTypeExtension();

		$functionReflectionMock = $this->createMock(FunctionReflection::class);
		$functionReflectionMock
			->method('getName')
			->willReturn($functionName);

		$scopeMock = $this->createMock(Scope::class);
		$scopeMock
			->method('getType')
			->willReturn(new ArrayType(new IntegerType(), new StringType()));

		$this->assertTrue($extension->isFunctionSupported($functionReflectionMock));
		$this->assertSame(
			$expectedDescription,
			$extension->getTypeFromFunctionCall($functionReflectionMock, $functionCall, $scopeMock)->describe()
		);
	}

}
