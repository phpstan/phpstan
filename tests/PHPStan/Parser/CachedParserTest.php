<?php declare(strict_types = 1);

namespace PHPStan\Parser;

class CachedParserTest extends \PHPUnit\Framework\TestCase
{

	/**
	 * @dataProvider dataParseFileClearCache
	 * @param int $cachedNodesByFileCountMax
	 * @param int $cachedNodesByStringCountMax
	 * @param int $cachedNodesByFileCountExpected
	 * @param int $cachedNodesByStringCountExpected
	 */
	public function testParseFileClearCache(
		int $cachedNodesByFileCountMax,
		int $cachedNodesByStringCountMax,
		int $cachedNodesByFileCountExpected,
		int $cachedNodesByStringCountExpected
	): void
	{
		$parser = new CachedParser(
			$this->getParserMock(),
			$cachedNodesByFileCountMax,
			$cachedNodesByStringCountMax
		);

		$this->assertEquals(
			$cachedNodesByFileCountMax,
			$parser->getCachedNodesByFileCountMax()
		);

		$this->assertEquals(
			$cachedNodesByStringCountMax,
			$parser->getCachedNodesByStingCountMax()
		);

		// Add files to cache
		for ($i = 0; $i <= $cachedNodesByFileCountMax; $i++) {
			$parser->parseFile('file' . $i);
		}

		$this->assertEquals(
			$cachedNodesByFileCountExpected,
			$parser->getCachedNodesByFileCount()
		);

		$this->assertCount(
			$cachedNodesByFileCountExpected,
			$parser->getCachedNodesByFile()
		);

		// Add strings to cache
		for ($i = 0; $i <= $cachedNodesByStringCountMax; $i++) {
			$parser->parseString('string' . $i);
		}

		$this->assertEquals(
			$cachedNodesByStringCountExpected,
			$parser->getCachedNodesByStringCount()
		);

		$this->assertCount(
			$cachedNodesByStringCountExpected,
			$parser->getCachedNodesByString()
		);
	}

	public function testCachedSourceCodes(): void
	{
		$parser = new CachedParser(
			$this->getParserMock(),
			10,
			10
		);
		$parser->setCachedSourceCodesByFile([
			'file1' => '<?php echo 1;',
		]);
		$parser->parseFile('file1');
		$cachedNodesByFile = $parser->getCachedNodesByFile();
		$cachedNodesByString = $parser->getCachedNodesByString();
		$this->assertEquals(true, isset($cachedNodesByFile['file1']));
		$this->assertEquals(true, isset($cachedNodesByString['<?php echo 1;']));
	}

	public function dataParseFileClearCache(): \Generator
	{
		yield 'even' => [
			'cachedNodesByFileCountMax' => 100,
			'cachedNodesByStringCountMax' => 50,
			'cachedNodesByFileCountExpected' => 100,
			'cachedNodesByStringCountExpected' => 50,
		];

		yield 'odd' => [
			'cachedNodesByFileCountMax' => 101,
			'cachedNodesByStringCountMax' => 51,
			'cachedNodesByFileCountExpected' => 101,
			'cachedNodesByStringCountExpected' => 51,
		];
	}

	/**
	 * @return Parser&\PHPUnit\Framework\MockObject\MockObject
	 */
	private function getParserMock(): Parser
	{
		$mock = $this->createMock(Parser::class);

		$mock->method('parseFile')->willReturn([$this->getPhpParserNodeMock()]);
		$mock->method('parseString')->willReturn([$this->getPhpParserNodeMock()]);

		return $mock;
	}

	/**
	 * @return \PhpParser\Node&\PHPUnit\Framework\MockObject\MockObject
	 */
	private function getPhpParserNodeMock(): \PhpParser\Node
	{
		return $this->createMock(\PhpParser\Node::class);
	}

}
