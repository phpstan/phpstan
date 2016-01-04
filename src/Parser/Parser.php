<?php declare(strict_types = 1);

namespace PHPStan\Parser;

interface Parser
{

	/**
	 * @param string $file path to a file to parse
	 * @return \PhpParser\Node[]
	 */
	public function parse(string $file): array;

}
