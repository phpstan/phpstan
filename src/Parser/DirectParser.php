<?php declare(strict_types = 1);

namespace PHPStan\Parser;

use PhpParser\NodeTraverser;

class DirectParser implements Parser
{

	/**
	 * @var \PhpParser\Parser
	 */
	private $parser;

	/**
	 * @var \PhpParser\NodeTraverser
	 */
	private $traverser;

	public function __construct(\PhpParser\Parser $parser, NodeTraverser $traverser)
	{
		$this->parser = $parser;
		$this->traverser = $traverser;
	}

	/**
	 * @param string $file path to a file to parse
	 * @return \PhpParser\Node[]
	 */
	public function parseFile(string $file): array
	{
		return $this->parseString(file_get_contents($file));
	}

	/**
	 * @param string $sourceCode
	 * @return \PhpParser\Node[]
	 */
	public function parseString(string $sourceCode): array
	{
		return $this->traverser->traverse($this->parser->parse($sourceCode));
	}

}
