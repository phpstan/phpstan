<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class LookForAssignsSettingsTest extends \PHPStan\Testing\TestCase
{

	public function dataShouldSkipBranch(): array
	{
		return [
			[
				LookForAssignsSettings::default(),
				new \PhpParser\Node\Stmt\Return_(),
				true,
			],
			[
				LookForAssignsSettings::default(),
				new \PhpParser\Node\Stmt\Continue_(),
				true,
			],
			[
				LookForAssignsSettings::default(),
				new \PhpParser\Node\Stmt\Break_(),
				true,
			],
			[
				LookForAssignsSettings::insideSwitch(),
				new \PhpParser\Node\Stmt\Return_(),
				true,
			],
			[
				LookForAssignsSettings::insideSwitch(),
				new \PhpParser\Node\Stmt\Continue_(),
				false,
			],
			[
				LookForAssignsSettings::insideSwitch(),
				new \PhpParser\Node\Stmt\Break_(),
				false,
			],
			[
				LookForAssignsSettings::insideLoop(),
				new \PhpParser\Node\Stmt\Return_(),
				true,
			],
			[
				LookForAssignsSettings::insideLoop(),
				new \PhpParser\Node\Stmt\Continue_(),
				false,
			],
			[
				LookForAssignsSettings::insideLoop(),
				new \PhpParser\Node\Stmt\Break_(),
				true,
			],
			[
				LookForAssignsSettings::insideFinally(),
				new \PhpParser\Node\Stmt\Return_(),
				false,
			],
			[
				LookForAssignsSettings::insideFinally(),
				new \PhpParser\Node\Stmt\Continue_(),
				false,
			],
			[
				LookForAssignsSettings::insideFinally(),
				new \PhpParser\Node\Stmt\Break_(),
				false,
			],
		];
	}

	/**
	 * @dataProvider dataShouldSkipBranch
	 * @param \PHPStan\Analyser\LookForAssignsSettings $settings
	 * @param \PhpParser\Node $earlyTerminationStatement
	 * @param bool $expectedResult
	 */
	public function testShouldSkipBranch(
		LookForAssignsSettings $settings,
		\PhpParser\Node $earlyTerminationStatement,
		bool $expectedResult
	)
	{
		$this->assertSame(
			$expectedResult,
			$settings->shouldSkipBranch($earlyTerminationStatement)
		);
	}

	public function dataShouldIntersectVariables(): array
	{
		return [
			[
				LookForAssignsSettings::default(),
				null,
				true,
			],
			[
				LookForAssignsSettings::insideSwitch(),
				null,
				true,
			],
			[
				LookForAssignsSettings::insideSwitch(),
				new \PhpParser\Node\Stmt\Continue_(),
				true,
			],
			[
				LookForAssignsSettings::insideSwitch(),
				new \PhpParser\Node\Stmt\Break_(),
				true,
			],
			[
				LookForAssignsSettings::insideLoop(),
				null,
				true,
			],
			[
				LookForAssignsSettings::insideLoop(),
				new \PhpParser\Node\Stmt\Continue_(),
				true,
			],
			[
				LookForAssignsSettings::insideLoop(),
				new \PhpParser\Node\Stmt\Break_(),
				true,
			],
			[
				LookForAssignsSettings::insideFinally(),
				null,
				true,
			],
			[
				LookForAssignsSettings::insideFinally(),
				new \PhpParser\Node\Stmt\Return_(),
				true,
			],
			[
				LookForAssignsSettings::insideFinally(),
				new \PhpParser\Node\Stmt\Continue_(),
				true,
			],
			[
				LookForAssignsSettings::insideFinally(),
				new \PhpParser\Node\Stmt\Break_(),
				true,
			],
		];
	}

	/**
	 * @dataProvider dataShouldIntersectVariables
	 * @param \PHPStan\Analyser\LookForAssignsSettings $settings
	 * @param \PhpParser\Node|null $earlyTerminationStatement
	 * @param bool $expectedResult
	 */
	public function testShouldIntersectVariables(
		LookForAssignsSettings $settings,
		\PhpParser\Node $earlyTerminationStatement = null,
		bool $expectedResult
	)
	{
		$this->assertSame(
			$expectedResult,
			$settings->shouldIntersectVariables($earlyTerminationStatement)
		);
	}

}
