<?php declare(strict_types = 1);

namespace PHPStan\Command;

use PHPStan\Analyser\Error;
use PHPStan\Testing\TestCase;

/**
 * @covers \PHPStan\Command\AnalysisResult
 */
final class AnalysisResultTest extends TestCase
{

	public function testErrorsAreSortedByFileNameAndLine(): void
	{
		self::assertEquals(
			[
				new Error('aa1', 'aaa'),
				new Error('aa2', 'aaa', 10),
				new Error('aa3', 'aaa', 15),
				new Error('aa4', 'aaa', 16),
				new Error('aa5', 'aaa', 16),
				new Error('aa6', 'aaa', 16),
				new Error('bb2', 'bbb', 2),
				new Error('bb1', 'bbb', 4),
				new Error('ccc', 'ccc'),
				new Error('ddd', 'ddd'),
			],
			(new AnalysisResult(
				[
					new Error('bb1', 'bbb', 4),
					new Error('bb2', 'bbb', 2),
					new Error('aa1', 'aaa'),
					new Error('ddd', 'ddd'),
					new Error('ccc', 'ccc'),
					new Error('aa2', 'aaa', 10),
					new Error('aa3', 'aaa', 15),
					new Error('aa5', 'aaa', 16),
					new Error('aa6', 'aaa', 16),
					new Error('aa4', 'aaa', 16),
				],
				[],
				false,
				__DIR__
			))->getFileSpecificErrors()
		);
	}

}
