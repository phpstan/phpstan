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
		$lineOffset = 39;
		$lineOffset2 = 74;

		$this->analyse([__DIR__ . '/../data/callable-exists.php'], [
					[
				'Argument #1 of call_user_func should be callable, but passed array has too much items, only two items expected.',
				$lineOffset, //109
					],
					[
					'Argument #1 of call_user_func should be callable, but passed array doesn\'t have required 2 items.',
					$lineOffset + 1, //110
					],
					[
					'Argument #1 of call_user_func should be callable, but passed static method unknownTarget::method does not exists.',
					$lineOffset + 2, //111
					],
					[
					'Argument #1 of call_user_func should be callable, but passed static method Unknown::method does not exists.',
					$lineOffset + 3, //112
					],
			//113
					[
					'Argument #1 of call_user_func should be callable, but passed method $this(CallableExists\Bar)::unknownMethod does not exists.',
					$lineOffset + 5, //114
					],
					[
					'Argument #1 of call_user_func should be callable, but passed static method $this(CallableExists\Bar)::knownStaticMethod as non-static.',
					$lineOffset + 6, //115
					],
			//116
			//117
			//118
					[
					'Argument #1 of call_user_func should be callable, but passed method $this(CallableExists\Bar)::unknownMethod does not exists.',
					$lineOffset + 10, //119
					],
			// 120
			// 121
			// 122
			// 123
			// 124
					[
					'Argument #1 of call_user_func should be callable, but passed method CallableExists\Foo::unknownMethod does not exists.',
					$lineOffset + 16, //125
					],
					[
					'Argument #1 of call_user_func should be callable, but passed static method CallableExists\Foo::knownStaticMethod as non-static.',
					$lineOffset + 17, //126
					],
			//127
					[
					'Argument #1 of call_user_func should be callable, but passed static method CallableExists\Bar::unknownStaticMethod does not exists.',
					$lineOffset + 19, //128
					],
					[
					'Argument #1 of call_user_func should be callable, but passed non-static method CallableExists\Bar::knownMethod as static.',
					$lineOffset + 20, //129
					],
			//130
					[
					'Argument #1 of call_user_func should be callable, but passed static method \CallableExists\Bar::unknownStaticMethod does not exists.',
					$lineOffset + 22, //131
					],
					[
					'Argument #1 of call_user_func should be callable, but passed non-static method \CallableExists\Bar::knownMethod as static.',
					$lineOffset + 23, //132
					],
			//133
					[
					'Argument #1 of call_user_func should be callable, but passed static method \CallableExists\Bar::unknownStaticMethod does not exists.',
					$lineOffset + 25, //134
					],
					[
					'Argument #1 of call_user_func should be callable, but passed non-static method \CallableExists\Bar::knownMethod as static.',
					$lineOffset + 26, //135
					],
			//136
					[
					'Argument #1 of call_user_func should be callable, but passed static method \CallableExists\Bar::unknownStaticMethod does not exists.',
					$lineOffset + 28, //137
					],
					[
					'Argument #1 of call_user_func should be callable, but passed non-static method \CallableExists\Bar::knownMethod as static.',
					$lineOffset + 29, //138
					],
			//139
					[
					'Argument #1 of call_user_func should be callable, but passed unknownFunction is not a existing function name.',
					$lineOffset + 31, //140
					],
			//141
					[
					'Argument #1 of call_user_func should be callable, but passed unknownFunction is not a existing function name.',
					$lineOffset + 33, //142
					],
			// ------------
					[
					'Argument #1 of funcWithCallableParam should be callable, but passed array has too much items, only two items expected.',
					$lineOffset2, //109
					],
					[
					'Argument #1 of funcWithCallableParam should be callable, but passed array doesn\'t have required 2 items.',
					$lineOffset2 + 1, //110
					],
					[
					'Argument #1 of funcWithCallableParam should be callable, but passed static method unknownTarget::method does not exists.',
					$lineOffset2 + 2, //111
					],
					[
					'Argument #1 of funcWithCallableParam should be callable, but passed static method Unknown::method does not exists.',
					$lineOffset2 + 3, //112
					],
			//113
					[
					'Argument #1 of funcWithCallableParam should be callable, but passed method $this(CallableExists\Bar)::unknownMethod does not exists.',
					$lineOffset2 + 5, //114
					],
					[
					'Argument #1 of funcWithCallableParam should be callable, but passed static method $this(CallableExists\Bar)::knownStaticMethod as non-static.',
					$lineOffset2 + 6, //115
					],
			//116
			//117
			//118
					[
					'Argument #1 of funcWithCallableParam should be callable, but passed method $this(CallableExists\Bar)::unknownMethod does not exists.',
					$lineOffset2 + 10, //119
					],
			// 120
			// 121
			// 122
			// 123
			// 124
					[
					'Argument #1 of funcWithCallableParam should be callable, but passed method CallableExists\Foo::unknownMethod does not exists.',
					$lineOffset2 + 16, //125
					],
					[
					'Argument #1 of funcWithCallableParam should be callable, but passed static method CallableExists\Foo::knownStaticMethod as non-static.',
					$lineOffset2 + 17, //126
					],
			//127
					[
					'Argument #1 of funcWithCallableParam should be callable, but passed static method CallableExists\Bar::unknownStaticMethod does not exists.',
					$lineOffset2 + 19, //128
					],
					[
					'Argument #1 of funcWithCallableParam should be callable, but passed non-static method CallableExists\Bar::knownMethod as static.',
					$lineOffset2 + 20, //129
					],
			//130
					[
					'Argument #1 of funcWithCallableParam should be callable, but passed static method \CallableExists\Bar::unknownStaticMethod does not exists.',
					$lineOffset2 + 22, //131
					],
					[
					'Argument #1 of funcWithCallableParam should be callable, but passed non-static method \CallableExists\Bar::knownMethod as static.',
					$lineOffset2 + 23, //132
					],
			//133
					[
					'Argument #1 of funcWithCallableParam should be callable, but passed static method \CallableExists\Bar::unknownStaticMethod does not exists.',
					$lineOffset2 + 25, //134
					],
					[
					'Argument #1 of funcWithCallableParam should be callable, but passed non-static method \CallableExists\Bar::knownMethod as static.',
					$lineOffset2 + 26, //135
					],
			//136
					[
					'Argument #1 of funcWithCallableParam should be callable, but passed static method \CallableExists\Bar::unknownStaticMethod does not exists.',
					$lineOffset2 + 28, //137
					],
					[
					'Argument #1 of funcWithCallableParam should be callable, but passed non-static method \CallableExists\Bar::knownMethod as static.',
					$lineOffset2 + 29, //138
					],
			//139
					[
					'Argument #1 of funcWithCallableParam should be callable, but passed unknownFunction is not a existing function name.',
					$lineOffset2 + 31, //140
					],
			//141
					[
					'Argument #1 of funcWithCallableParam should be callable, but passed unknownFunction is not a existing function name.',
					$lineOffset2 + 33, //142
					],
		]);
	}

}
