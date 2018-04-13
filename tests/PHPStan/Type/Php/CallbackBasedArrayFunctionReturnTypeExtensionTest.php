<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\StringType;
use PHPStan\Type\VerbosityLevel;

class CallbackBasedArrayFunctionReturnTypeExtensionTest extends \PHPStan\Testing\TestCase
{

	/**
	 * @return mixed[]
	 */
	public function dataFunctions(): array
	{
		return [
			[
				'array_map',
				[
					new Arg(new Closure([
						'returnType' => 'string',
					])),
				],
				'array<string>',
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
		$extension = new CallbackBasedArrayFunctionReturnTypeExtension();

		$functionReflectionMock = $this->createMock(FunctionReflection::class);
		$functionReflectionMock
			->method('getName')
			->willReturn($functionName);

		$scopeMock = $this->createMock(Scope::class);
		$scopeMock
			->method('getFunctionType')
			->with('string', false, false)
			->willReturn(new StringType());

		$scopeMock
			->method('getType')
			->willReturn(new StringType());

		$this->assertTrue($extension->isFunctionSupported($functionReflectionMock));
		$this->assertSame(
			$expectedDescription,
			$extension->getTypeFromFunctionCall($functionReflectionMock, $functionCall, $scopeMock)->describe(VerbosityLevel::value())
		);
	}

}
