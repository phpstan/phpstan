<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node\Expr\ClassConstFetch;
use PHPStan\Analyser\Node;
use PHPStan\Broker\Broker;

class ClassConstantRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @param \PHPStan\Broker\Broker $broker
	 */
	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function getNodeType(): string
	{
		return ClassConstFetch::class;
	}

	/**
	 * @param \PHPStan\Analyser\Node $node
	 * @return string[]
	 */
	public function processNode(Node $node): array
	{
		$constantFetch = $node->getParserNode();
		$class = $constantFetch->class;
		if (!($class instanceof \PhpParser\Node\Name)) {
			return [];
		}
		$className = (string) $class;

		if ($className === 'self' || $className === 'static') {
			if ($node->getScope()->getClass() === null) {
				return [
					sprintf('Using %s outside of class scope.', $className),
				];
			} else {
				return [];
			}
		}

		if (!$this->broker->hasClass($className)) {
			return [
				sprintf('Class %s does not exist.', $className),
			];
		}

		$constantName = $constantFetch->name;
		if ($constantName === 'class') {
			return [];
		}

		$classReflection = $this->broker->getClass($className);
		if (!$classReflection->hasConstant($constantName)) {
			return [
				sprintf('Access to undefined constant %s::%s.', $className, $constantName),
			];
		}

		return [];
	}

}
