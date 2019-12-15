<?php declare(strict_types = 1);

namespace App;

use function file_get_contents;
use PHPUnit\Framework\TestCase;

class CodeSanitizerTest extends TestCase
{

	public function dataSanitize(): array
	{
		return [
			[
				__DIR__ . '/data/function-generator-in.php',
				__DIR__ . '/data/function-generator-out.php',
			],
			[
				__DIR__ . '/data/method-generator-in.php',
				__DIR__ . '/data/method-generator-out.php',
			],
			[
				__DIR__ . '/data/use-in.php',
				__DIR__ . '/data/use-out.php',
			],
			[
				__DIR__ . '/data/use-in-namespace-in.php',
				__DIR__ . '/data/use-in-namespace-out.php',
			],
			[
				__DIR__ . '/data/trait-in.php',
				__DIR__ . '/data/trait-out.php',
			],
		];
	}

	/**
	 * @dataProvider dataSanitize
	 * @param string $inputFile
	 * @param string $outputFile
	 */
	public function testSanitize(string $inputFile, string $outputFile)
	{
		$errorHandler = new \PhpParser\ErrorHandler\Collecting();
		$sanitizer = new \App\CodeSanitizer(
			new \PhpParser\Parser\Php7(new \PhpParser\Lexer()),
			new \PhpParser\PrettyPrinter\Standard(),
			$errorHandler
		);
		$sanitizedCode = $sanitizer->sanitize(file_get_contents($inputFile));
		$this->assertStringEqualsFile($outputFile, $sanitizedCode);
	}

}
