<?php declare(strict_types = 1);

namespace PHPStan\Command;

use PHPStan\Analyser\Error;
use PHPStan\Testing\TestCase;

final class AnalysisResultTest extends TestCase
{

	public function testErrorsAreSortedByFileNameAndLine(): void
	{
		self::assertEquals(
			[
				new Error('aa1', 'aaa', __CLASS__),
				new Error('aa2', 'aaa', __CLASS__, 10),
				new Error('aa3', 'aaa', __CLASS__, 15),
				new Error('aa4', 'aaa', __CLASS__, 16),
				new Error('aa5', 'aaa', __CLASS__, 16),
				new Error('aa6', 'aaa', __CLASS__, 16),
				new Error('bb2', 'bbb', __CLASS__, 2),
				new Error('bb1', 'bbb', __CLASS__, 4),
				new Error('ccc', 'ccc', __CLASS__),
				new Error('ddd', 'ddd', __CLASS__),
			],
			(new AnalysisResult(
				[
					new Error('bb1', 'bbb', __CLASS__, 4),
					new Error('bb2', 'bbb', __CLASS__, 2),
					new Error('aa1', 'aaa', __CLASS__),
					new Error('ddd', 'ddd', __CLASS__),
					new Error('ccc', 'ccc', __CLASS__),
					new Error('aa2', 'aaa', __CLASS__, 10),
					new Error('aa3', 'aaa', __CLASS__, 15),
					new Error('aa5', 'aaa', __CLASS__, 16),
					new Error('aa6', 'aaa', __CLASS__, 16),
					new Error('aa4', 'aaa', __CLASS__, 16),
				],
				[],
				false,
				__DIR__
			))->getFileSpecificErrors()
		);
	}

}
