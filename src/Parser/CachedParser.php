<?php declare(strict_types = 1);

namespace PHPStan\Parser;

class CachedParser implements Parser
{

	/** @var \PHPStan\Parser\Parser */
	private $originalParser;

	/** @var mixed[] */
	private $cachedNodesByFile = [];

	/** @var mixed[] */
	private $cachedNodesByString = [];

	public function __construct(Parser $originalParser)
	{
		$this->originalParser = $originalParser;
	}

	/**
	 * @param string $file path to a file to parse
	 * @return \PhpParser\Node[]
	 */
	public function parseFile(string $file): array
	{
		if (!isset($this->cachedNodesByFile[$file])) {
			$this->cachedNodesByFile[$file] = $this->originalParser->parseFile($file);
		}

		return $this->cachedNodesByFile[$file];
	}

	/**
	 * @param string $sourceCode
	 * @return \PhpParser\Node[]
	 */
	public function parseString(string $sourceCode): array
	{
		if (!isset($this->cachedNodesByString[$sourceCode])) {
			$this->cachedNodesByString[$sourceCode] = $this->originalParser->parseString($sourceCode);
		}

		return $this->cachedNodesByString[$sourceCode];
	}

}
