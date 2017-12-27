<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\CallableExistsCheck;

class FunctionCallableArgExistsRuleTest extends \PHPStan\Testing\RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		return new FunctionCallableArgExistsRule(
			$broker,
			new CallableExistsCheck($broker)
		);
	}

	public function testCallableExists()
	{
		$lineOffset = 58;
		$lineOffset2 = 124;

		$this->analyse([__DIR__ . '/../data/callable-exists.php'], [
			[
				'Argument #1 function_name of call_user_func should be callable, but passed array has too many items, only two items expected.',
				$lineOffset,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed array doesn\'t have required 2 items.',
				$lineOffset + 1,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed static method unknownTarget::method does not exists.',
				$lineOffset + 2,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed array is not valid callback.',
				$lineOffset + 3,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed static method Unknown::method does not exists.',
				$lineOffset + 4,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed method $this(CallableExists\Bar)::unknownMethod does not exists.',
				$lineOffset + 6,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed static method $this(CallableExists\Bar)::knownStaticMethod as non-static.',
				$lineOffset + 7,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed method $this(CallableExists\Bar)::unknownMethod does not exists.',
				$lineOffset + 11,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed method CallableExists\Foo::unknownMethod does not exists.',
				$lineOffset + 18,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed static method CallableExists\Foo::knownStaticMethod as non-static.',
				$lineOffset + 19,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed static method CallableExists\Bar::unknownStaticMethod does not exists.',
				$lineOffset + 21,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed non-static method CallableExists\Bar::knownMethod as static.',
				$lineOffset + 22,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed static method \CallableExists\Bar::unknownStaticMethod does not exists.',
				$lineOffset + 24,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed non-static method \CallableExists\Bar::knownMethod as static.',
				$lineOffset + 25,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed static method \CallableExists\Bar::unknownStaticMethod does not exists.',
				$lineOffset + 27,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed non-static method \CallableExists\Bar::knownMethod as static.',
				$lineOffset + 28,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed string is not valid callback.',
				$lineOffset + 29,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed string is not valid callback.',
				$lineOffset + 30,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed static method \CallableExists\Bar::unknownStaticMethod does not exists.',
				$lineOffset + 32,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed non-static method \CallableExists\Bar::knownMethod as static.',
				$lineOffset + 33,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed function unknownFunction not found.',
				$lineOffset + 36,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed function unknownFunction not found.',
				$lineOffset + 38,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed method CallableExists\Foo::unknownMethod does not exists.',
				$lineOffset + 41,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed static method CallableExists\Foo::knownStaticMethod as non-static.',
				$lineOffset + 42,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed method CallableExists\Foo::unknownMethod does not exists.',
				$lineOffset + 44,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed static method CallableExists\Foo::knownStaticMethod as non-static.',
				$lineOffset + 45,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed array is not valid callback.',
				$lineOffset + 59,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed array is not valid callback.',
				$lineOffset + 60,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed value is not valid callback.',
				$lineOffset + 61,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed static method CallableExists\Bar::unknownStaticMethod does not exists.',
				$lineOffset + 63,
			],
			[
				'Argument #1 function_name of call_user_func should be callable, but passed non-static method CallableExists\Bar::knownMethod as static.',
				$lineOffset + 64,
			],
			// ----
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed array has too many items, only two items expected.',
				$lineOffset2,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed array doesn\'t have required 2 items.',
				$lineOffset2 + 1,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed static method unknownTarget::method does not exists.',
				$lineOffset2 + 2,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed array is not valid callback.',
				$lineOffset2 + 3,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed static method Unknown::method does not exists.',
				$lineOffset2 + 4,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed method $this(CallableExists\Bar)::unknownMethod does not exists.',
				$lineOffset2 + 6,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed static method $this(CallableExists\Bar)::knownStaticMethod as non-static.',
				$lineOffset2 + 7,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed method $this(CallableExists\Bar)::unknownMethod does not exists.',
				$lineOffset2 + 11,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed method CallableExists\Foo::unknownMethod does not exists.',
				$lineOffset2 + 18,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed static method CallableExists\Foo::knownStaticMethod as non-static.',
				$lineOffset2 + 19,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed static method CallableExists\Bar::unknownStaticMethod does not exists.',
				$lineOffset2 + 21,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed non-static method CallableExists\Bar::knownMethod as static.',
				$lineOffset2 + 22,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed static method \CallableExists\Bar::unknownStaticMethod does not exists.',
				$lineOffset2 + 24,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed non-static method \CallableExists\Bar::knownMethod as static.',
				$lineOffset2 + 25,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed static method \CallableExists\Bar::unknownStaticMethod does not exists.',
				$lineOffset2 + 27,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed non-static method \CallableExists\Bar::knownMethod as static.',
				$lineOffset2 + 28,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed string is not valid callback.',
				$lineOffset2 + 29,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed string is not valid callback.',
				$lineOffset2 + 30,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed static method \CallableExists\Bar::unknownStaticMethod does not exists.',
				$lineOffset2 + 32,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed non-static method \CallableExists\Bar::knownMethod as static.',
				$lineOffset2 + 33,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed function unknownFunction not found.',
				$lineOffset2 + 36,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed function unknownFunction not found.',
				$lineOffset2 + 38,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed method CallableExists\Foo::unknownMethod does not exists.',
				$lineOffset2 + 41,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed static method CallableExists\Foo::knownStaticMethod as non-static.',
				$lineOffset2 + 42,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed method CallableExists\Foo::unknownMethod does not exists.',
				$lineOffset2 + 44,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed static method CallableExists\Foo::knownStaticMethod as non-static.',
				$lineOffset2 + 45,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed array is not valid callback.',
				$lineOffset2 + 59,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed array is not valid callback.',
				$lineOffset2 + 60,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed value is not valid callback.',
				$lineOffset2 + 61,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed static method CallableExists\Bar::unknownStaticMethod does not exists.',
				$lineOffset2 + 63,
			],
			[
				'Argument #1 callableParam of funcWithCallableParam should be callable, but passed non-static method CallableExists\Bar::knownMethod as static.',
				$lineOffset2 + 64,
			],
		]);
	}

}
