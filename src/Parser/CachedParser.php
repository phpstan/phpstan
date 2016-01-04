<?php declare(strict_types = 1);

namespace PHPStan\Parser;

class CachedParser implements Parser
{

	/** @var \PHPStan\Parser\Parser */
	private $originalParser;

	/** @var mixed[] */
	private $cachedNodes = [];

	public function __construct(Parser $originalParser)
	{
		$this->originalParser = $originalParser;
	}

	/**
	 * @param string $file path to a file to parse
	 * @return \PhpParser\Node[]
	 */
	public function parse(string $file): array
	{
		if (!isset($this->cachedNodes[$file])) {
			$this->cachedNodes[$file] = $this->originalParser->parse($file);
		}

		return $this->cachedNodes[$file];
	}

}
