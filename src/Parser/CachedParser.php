<?php declare(strict_types = 1);

namespace PHPStan\Parser;

class CachedParser implements Parser
{

	/** @var \PHPStan\Parser\Parser */
	private $originalParser;

	/** @var mixed[] */
	private $cachedNodesByFile = [];

	/** @var int */
	private $cachedNodesByFileCount = 0;

	/** @var int */
	private $cachedNodesByFileCountMax;

	/** @var mixed[] */
	private $cachedNodesByString = [];

	/** @var int */
	private $cachedNodesByStringCount = 0;

	/** @var int */
	private $cachedNodesByStringCountMax;

	public function __construct(
		Parser $originalParser,
		int $cachedNodesByFileCountMax,
		int $cachedNodesByStringCountMax
	)
	{
		$this->originalParser = $originalParser;
		$this->cachedNodesByFileCountMax = $cachedNodesByFileCountMax;
		$this->cachedNodesByStringCountMax = $cachedNodesByStringCountMax;
	}

	/**
	 * @param string $file path to a file to parse
	 * @return \PhpParser\Node[]
	 */
	public function parseFile(string $file): array
	{
		if ($this->cachedNodesByFileCountMax !== 0 && $this->cachedNodesByFileCount >= $this->cachedNodesByFileCountMax) {
			$this->cachedNodesByFile = array_slice(
				$this->cachedNodesByFile,
				1,
				null,
				true
			);

			--$this->cachedNodesByFileCount;
		}

		if (!isset($this->cachedNodesByFile[$file])) {
			$this->cachedNodesByFile[$file] = $this->originalParser->parseFile($file);
			$this->cachedNodesByFileCount++;
		}

		return $this->cachedNodesByFile[$file];
	}

	/**
	 * @param string $sourceCode
	 * @return \PhpParser\Node[]
	 */
	public function parseString(string $sourceCode): array
	{
		if ($this->cachedNodesByStringCountMax !== 0 && $this->cachedNodesByStringCount >= $this->cachedNodesByStringCountMax) {
			$this->cachedNodesByString = array_slice(
				$this->cachedNodesByString,
				1,
				null,
				true
			);

			--$this->cachedNodesByStringCount;
		}

		if (!isset($this->cachedNodesByString[$sourceCode])) {
			$this->cachedNodesByString[$sourceCode] = $this->originalParser->parseString($sourceCode);
			$this->cachedNodesByStringCount++;
		}

		return $this->cachedNodesByString[$sourceCode];
	}

	public function getCachedNodesByFileCount(): int
	{
		return $this->cachedNodesByFileCount;
	}

	public function getCachedNodesByFileCountMax(): int
	{
		return $this->cachedNodesByFileCountMax;
	}

	public function getCachedNodesByStringCount(): int
	{
		return $this->cachedNodesByStringCount;
	}

	public function getCachedNodesByStingCountMax(): int
	{
		return $this->cachedNodesByStringCountMax;
	}

	public function getCachedNodesByFile(): array
	{
		return $this->cachedNodesByFile;
	}

	public function getCachedNodesByString(): array
	{
		return $this->cachedNodesByString;
	}

}
