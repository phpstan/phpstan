<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class Node
{

	/**
	 * @var \PhpParser\Node
	 */
	private $parserNode;

	/**
	 * @var \PHPStan\Analyser\Scope
	 */
	private $scope;

	public function __construct(\PhpParser\Node $parserNode, Scope $scope)
	{
		$this->parserNode = $parserNode;
		$this->scope = $scope;
	}

	public function getParserNode(): \PhpParser\Node
	{
		return $this->parserNode;
	}

	public function getScope(): Scope
	{
		return $this->scope;
	}

}
