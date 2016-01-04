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

	/**
	 * @param \PhpParser\Parser $parser
	 * @param \PhpParser\NodeTraverser $traverser
	 */
	public function __construct(\PhpParser\Parser $parser, NodeTraverser $traverser)
	{
		$this->parser = $parser;
		$this->traverser = $traverser;
	}

	/**
	 * @param string $file path to a file to parse
	 * @return \PhpParser\Node[]
	 */
	public function parse(string $file): array
	{
		return $this->traverser->traverse($this->parser->parse(file_get_contents($file)));
	}

}
