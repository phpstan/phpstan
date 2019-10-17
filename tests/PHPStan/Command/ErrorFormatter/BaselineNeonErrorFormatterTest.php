<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use Nette\Neon\Neon;
use PHPStan\Analyser\Error;
use PHPStan\Command\AnalysisResult;
use PHPStan\File\SimpleRelativePathHelper;

class BaselineNeonErrorFormatterTest extends TestBaseFormatter
{

	public function dataFormatterOutputProvider(): iterable
	{
		yield [
			'No errors',
			0,
			0,
			0,
			[],
		];

		yield [
			'One file error',
			1,
			1,
			0,
			[
				[
					'message' => '#^Foo$#',
					'count' => 1,
					'path' => 'folder with unicode 😃/file name with "spaces" and unicode 😃.php',
				],
			],
		];

		yield [
			'Multiple file errors',
			1,
			4,
			0,
			[
				[
					'message' => '#^Bar$#',
					'count' => 1,
					'path' => 'folder with unicode 😃/file name with "spaces" and unicode 😃.php',
				],
				[
					'message' => '#^Foo$#',
					'count' => 1,
					'path' => 'folder with unicode 😃/file name with "spaces" and unicode 😃.php',
				],
				[
					'message' => '#^Foo$#',
					'count' => 1,
					'path' => 'foo.php',
				],
				[
					'message' => '#^Bar$#',
					'count' => 1,
					'path' => 'foo.php',
				],
			],
		];

		yield [
			'Multiple file, multiple generic errors',
			1,
			4,
			2,
			[
				[
					'message' => '#^Bar$#',
					'count' => 1,
					'path' => 'folder with unicode 😃/file name with "spaces" and unicode 😃.php',
				],
				[
					'message' => '#^Foo$#',
					'count' => 1,
					'path' => 'folder with unicode 😃/file name with "spaces" and unicode 😃.php',
				],
				[
					'message' => '#^Foo$#',
					'count' => 1,
					'path' => 'foo.php',
				],
				[
					'message' => '#^Bar$#',
					'count' => 1,
					'path' => 'foo.php',
				],
			],
		];
	}

	/**
	 * @dataProvider dataFormatterOutputProvider
	 *
	 * @param string $message
	 * @param int    $exitCode
	 * @param int    $numFileErrors
	 * @param int    $numGenericErrors
	 * @param mixed[] $expected
	 */
	public function testFormatErrors(
		string $message,
		int $exitCode,
		int $numFileErrors,
		int $numGenericErrors,
		array $expected
	): void
	{
		$formatter = new BaselineNeonErrorFormatter(new SimpleRelativePathHelper(self::DIRECTORY_PATH));

		$this->assertSame($exitCode, $formatter->formatErrors(
			$this->getAnalysisResult($numFileErrors, $numGenericErrors),
			$this->getErrorConsoleStyle()
		), sprintf('%s: response code do not match', $message));

		$this->assertSame(trim(Neon::encode(['parameters' => ['ignoreErrors' => $expected]], Neon::BLOCK)), trim($this->getOutputContent()), sprintf('%s: output do not match', $message));
	}


	public function testFormatErrorMessagesRegexEscape(): void
	{
		$formatter = new BaselineNeonErrorFormatter(new SimpleRelativePathHelper(self::DIRECTORY_PATH));

		$result  = new AnalysisResult(
			[new Error('Escape Regex with file # ~ \' ()', 'Testfile')],
			['Escape Regex without file # ~ <> \' ()'],
			false,
			false,
			null
		);
		$formatter->formatErrors(
			$result,
			$this->getErrorConsoleStyle()
		);

		self::assertSame(
			trim(Neon::encode(['parameters' => ['ignoreErrors' => [
				[
					'message' => "#^Escape Regex with file \\# ~ ' \\(\\)$#",
					'count' => 1,
					'path' => 'Testfile',
				],
			]]], Neon::BLOCK)),
			trim($this->getOutputContent())
		);
	}

}
