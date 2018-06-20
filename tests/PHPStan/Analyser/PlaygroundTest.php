<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use Nette\Utils\FileSystem;
use PHPStan\Cache\Cache;
use PHPStan\Cache\MemoryCacheStorage;
use PHPStan\File\FileHelper;
use PHPStan\Rules\Registry;
use PHPStan\Type\FileTypeMapper;

class PlaygroundTest extends \PHPStan\TestCase
{

	public function dataPlaygrounds(): array
	{
		return [
			[
				'81293b64b24831d9eaed3b3424e6ba1a',
			],
		];
	}

	/**
	 * @dataProvider dataPlaygrounds
	 * @param string $shaHex
	 * @param string[] $expectedErrors
	 */
	public function testPlaygrounds(string $shaHex, array $expectedErrors = [])
	{
		$tempDir = $this->getContainer()->parameters['tempDir'];
		$playgroundFile = sprintf('%s/playground/%s.php', $tempDir, $shaHex);

		if (!is_file($playgroundFile)) {
			$playgroundTempFile = sprintf('%s.tmp', $playgroundFile);
			$playgroundUrl = sprintf('https://phpstan.org/r/%s/input', $shaHex);
			$inputEncoded = file_get_contents($playgroundUrl);
			$inputDecoded = json_decode($inputEncoded);
			FileSystem::write($playgroundTempFile, $inputDecoded->phpCode);
			FileSystem::rename($playgroundTempFile, $playgroundFile);
		}

		require $playgroundFile;

		$analyzer = $this->createAnalyser();

		$actualErrors = array_map(
			function (Error $error): string {
				return sprintf('%02d: %s', $error->getLine(), $error->getMessage());
			},
			$analyzer->analyse([$playgroundFile], true)
		);

		$this->assertSame(implode("\n", $expectedErrors), implode("\n", $actualErrors));
	}

	private function createAnalyser(): \PHPStan\Analyser\Analyser
	{
		return $this->getContainer()->callMethod(function (
			FileHelper $fileHelper,
			Registry $registry,
			TypeSpecifier $typeSpecifier,
			\PhpParser\PrettyPrinter\Standard $printer
		) {
			$broker = $this->createBroker();
			$parser = $this->getParser();
			$cache = new Cache(new MemoryCacheStorage());

			$analyser = new Analyser(
				$broker,
				$parser,
				$registry,
				new NodeScopeResolver(
					$broker,
					$parser,
					$printer,
					new FileTypeMapper($parser, $cache),
					new \PhpParser\BuilderFactory(),
					$fileHelper,
					false,
					false,
					[]
				),
				$printer,
				$typeSpecifier,
				$fileHelper,
				[],
				null,
				false
			);

			return $analyser;
		});
	}

}
