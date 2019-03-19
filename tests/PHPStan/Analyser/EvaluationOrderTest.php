<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

class EvaluationOrderTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new EvaluationOrderRule();
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/data/evaluation-order.php'], [
			[
				'six',
				4,
			],
			[
				'one',
				5,
			],
			[
				'five',
				6,
			],
			[
				'two',
				7,
			],
			[
				'three',
				8,
			],
			[
				'four',
				9,
			],
		]);
	}

}
