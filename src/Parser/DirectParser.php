<?php declare(strict_types = 1);

namespace PHPStan\Parser;

use PhpParser\NodeTraverser;

class DirectParser implements Parser
{

	/** @var \PhpParser\Parser */
	private $parser;

	/** @var \PhpParser\NodeTraverser */
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
		$contents = file_get_contents($file);
		if ($contents === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		return $this->parseString($contents);
	}

	/**
	 * @param string $sourceCode
	 * @return \PhpParser\Node[]
	 */
	public function parseString(string $sourceCode): array
	{
		$nodes = $this->parser->parse($sourceCode);
		if ($nodes === null) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		return $this->traverser->traverse($nodes);
	}

}
