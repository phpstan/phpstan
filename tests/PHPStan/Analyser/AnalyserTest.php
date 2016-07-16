<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\Parser\DirectParser;
use PHPStan\Rules\AlwaysFailRule;
use PHPStan\Rules\Registry;

class AnalyserTest extends \PHPStan\TestCase
{

	public function dataExclude(): array
	{
		return [
			[
				__DIR__ . '/data/parse-error.php',
				[__DIR__],
				[],
				0,
			],
			[
				__DIR__ . '/data/func-call.php',
				[],
				[],
				0,
			],
			[
				__DIR__ . '/data/parse-error.php',
				[__DIR__ . '/*'],
				[],
				0,
			],
			[
				__DIR__ . '/data/parse-error.php',
				[__DIR__ . '/aaa'],
				[],
				1,
			],
			[
				__DIR__ . '/data/parse-error.php',
				[__DIR__ . '/aaa'],
				[
					'#Syntax error#',
				],
				0,
			],
		];
	}

	/**
	 * @dataProvider dataExclude
	 * @param string $filePath
	 * @param string[] $analyseExcludes
	 * @param string[] $ignoreErrors
	 * @param integer $errorsCount
	 */
	public function testExclude(string $filePath, array $analyseExcludes, array $ignoreErrors, int $errorsCount)
	{
		$analyser = $this->createAnalyser($analyseExcludes, $ignoreErrors);
		$result = $analyser->analyse([$filePath]);
		$this->assertInternalType('array', $result);
		$this->assertCount($errorsCount, $result);
	}

	public function testReturnErrorIfIgnoredMessagesDoesNotOccur()
	{
		$analyser = $this->createAnalyser([], ['#Unknown error#']);
		$result = $analyser->analyse([__DIR__ . '/data/empty.php']);
		$this->assertInternalType('array', $result);
		$this->assertSame([
			'Ignored error pattern #Unknown error# was not matched in reported errors.',
		], $result);
	}

	public function testReportInvalidIgnorePatternEarly()
	{
		$analyser = $this->createAnalyser([], ['#Regexp syntax error']);
		$result = $analyser->analyse([__DIR__ . '/data/parse-error.php']);
		$this->assertInternalType('array', $result);
		$this->assertSame([
			"No ending delimiter '#' found in pattern: #Regexp syntax error",
		], $result);
	}

	/**
	 * @param string[] $analyseExcludes
	 * @param string[] $ignoreErrors
	 * @return Analyser
	 */
	private function createAnalyser(array $analyseExcludes, array $ignoreErrors): \PHPStan\Analyser\Analyser
	{
		$registry = new Registry();
		$registry->register(new AlwaysFailRule());

		$traverser = new \PhpParser\NodeTraverser();
		$traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());

		$broker = $this->createBroker();
		$printer = new \PhpParser\PrettyPrinter\Standard();
		$analyser = new Analyser(
			$broker,
			new DirectParser(new \PhpParser\Parser\Php7(new \PhpParser\Lexer()), $traverser),
			$registry,
			new NodeScopeResolver(
				$broker,
				$printer,
				false,
				false,
				false,
				[]
			),
			$printer,
			$analyseExcludes,
			$ignoreErrors
		);

		return $analyser;
	}

}
