<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PHPStan\Rules\CallableExistsCheck;
use PHPStan\Rules\RuleLevelHelper;

class StaticMethodCallableArgExistsRuleTest extends \PHPStan\Testing\RuleTestCase
{

	/**
	 * @var bool
	 */
	private $checkThisOnly;

	protected function getRule(): \PHPStan\Rules\Rule
	{
		$broker = $this->createBroker();
		$ruleLevelHelper = new RuleLevelHelper($broker, true, $this->checkThisOnly, true);
		return new StaticMethodCallableArgExistsRule(
			$broker,
			new CallableExistsCheck($broker),
			$ruleLevelHelper
		);
	}

	public function testCallableExists()
	{
		$lineOffset  = 144;

		$this->checkThisOnly = false;
		$this->analyse([__DIR__ . '/../data/callable-exists.php'], [
			[
				'Argument #1 of staticMethodWithCallableParam should be callable, but passed array has too much items, only two items expected.',
				$lineOffset, //109
			],
			[
				'Argument #1 of staticMethodWithCallableParam should be callable, but passed array doesn\'t have required 2 items.',
				$lineOffset + 1, //110
			],
			[
				'Argument #1 of staticMethodWithCallableParam should be callable, but passed static method unknownTarget::method does not exists.',
				$lineOffset + 2, //111
			],
			[
				'Argument #1 of staticMethodWithCallableParam should be callable, but passed static method Unknown::method does not exists.',
				$lineOffset + 3, //112
			],
			//113
			[
				'Argument #1 of staticMethodWithCallableParam should be callable, but passed method $this(CallableExists\Bar)::unknownMethod does not exists.',
				$lineOffset + 5, //114
			],
			[
				'Argument #1 of staticMethodWithCallableParam should be callable, but passed static method $this(CallableExists\Bar)::knownStaticMethod as non-static.',
				$lineOffset + 6, //115
			],
			//116
			//117
			//118
			[
				'Argument #1 of staticMethodWithCallableParam should be callable, but passed method $this(CallableExists\Bar)::unknownMethod does not exists.',
				$lineOffset + 10, //119
			],
			// 120
			// 121
			// 122
			// 123
			// 124
			[
				'Argument #1 of staticMethodWithCallableParam should be callable, but passed method CallableExists\Foo::unknownMethod does not exists.',
				$lineOffset + 16, //125
			],
			[
				'Argument #1 of staticMethodWithCallableParam should be callable, but passed static method CallableExists\Foo::knownStaticMethod as non-static.',
				$lineOffset + 17, //126
			],
			//127
			[
				'Argument #1 of staticMethodWithCallableParam should be callable, but passed static method CallableExists\Bar::unknownStaticMethod does not exists.',
				$lineOffset + 19, //128
			],
			[
				'Argument #1 of staticMethodWithCallableParam should be callable, but passed non-static method CallableExists\Bar::knownMethod as static.',
				$lineOffset + 20, //129
			],
			//130
			[
				'Argument #1 of staticMethodWithCallableParam should be callable, but passed static method \CallableExists\Bar::unknownStaticMethod does not exists.',
				$lineOffset + 22, //131
			],
			[
				'Argument #1 of staticMethodWithCallableParam should be callable, but passed non-static method \CallableExists\Bar::knownMethod as static.',
				$lineOffset + 23, //132
			],
			//133
			[
				'Argument #1 of staticMethodWithCallableParam should be callable, but passed static method \CallableExists\Bar::unknownStaticMethod does not exists.',
				$lineOffset + 25, //134
			],
			[
				'Argument #1 of staticMethodWithCallableParam should be callable, but passed non-static method \CallableExists\Bar::knownMethod as static.',
				$lineOffset + 26, //135
			],
			//136
			[
				'Argument #1 of staticMethodWithCallableParam should be callable, but passed static method \CallableExists\Bar::unknownStaticMethod does not exists.',
				$lineOffset + 28, //137
			],
			[
				'Argument #1 of staticMethodWithCallableParam should be callable, but passed non-static method \CallableExists\Bar::knownMethod as static.',
				$lineOffset + 29, //138
			],
			//139
			[
				'Argument #1 of staticMethodWithCallableParam should be callable, but passed unknownFunction is not a existing function name.',
				$lineOffset + 31, //140
			],
			//141
			[
				'Argument #1 of staticMethodWithCallableParam should be callable, but passed unknownFunction is not a existing function name.',
				$lineOffset + 33, //142
			],
		]);
	}

}
