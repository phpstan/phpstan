<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Node;
use PHPStan\Rules\FunctionDefinitionCheck;

class ExistingClassesInTypehintsRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\FunctionDefinitionCheck */
	private $check;

	public function __construct(FunctionDefinitionCheck $check)
	{
		$this->check = $check;
	}

	public function getNodeType(): string
	{
		return ClassMethod::class;
	}

	/**
	 * @param \PHPStan\Analyser\Node $node
	 * @return string[]
	 */
	public function processNode(Node $node): array
	{
		return $this->check->checkFunction(
			$node->getParserNode(),
			sprintf(
				'Parameter $%%s of method %s::%s() has invalid typehint type %%s.',
				$node->getScope()->getClass(),
				$node->getScope()->getFunction()
			),
			sprintf(
				'Return typehint of method %s::%s() has invalid type %%s.',
				$node->getScope()->getClass(),
				$node->getScope()->getFunction()
			)
		);
	}

}
