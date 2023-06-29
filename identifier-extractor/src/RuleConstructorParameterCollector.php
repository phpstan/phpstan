<?php declare(strict_types = 1);

namespace IdentifierExtractor;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\InClassMethodNode;
use PHPStan\PhpDocParser\Printer\Printer;
use function count;
use function strpos;

/**
 * @implements Collector<InClassMethodNode, array{class: string, parameters: non-empty-list<string>}>
 */
class RuleConstructorParameterCollector implements Collector
{

	public function getNodeType(): string
	{
		return InClassMethodNode::class;
	}

	public function processNode(Node $node, Scope $scope)
	{
		$classReflection = $node->getClassReflection();
		if (strpos($classReflection->getName(), 'PHPStan\\Rules\\') !== 0) {
			return null;
		}

		if (!$classReflection->hasConstructor()) {
			return null;
		}

		$methodReflection = $node->getMethodReflection();
		if ($classReflection->getConstructor()->getName() !== $methodReflection->getName()) {
			return null;
		}

		$parameters = [];
		foreach ($methodReflection->getVariants()[0]->getParameters() as $parameter) {
			if (!$parameter->getType()->isObject()->yes()) {
				continue;
			}

			$classNames = [];
			foreach ($parameter->getType()->getObjectClassNames() as $className) {
				if (strpos($className, 'PHPStan\\Rules\\') !== 0) {
					continue;
				}

				$classNames[] = $className;
			}

			foreach ($classNames as $className) {
				$parameters[] = $className;
			}
		}

		if (count($parameters) === 0) {
			return null;
		}

		return [
			'class' => $classReflection->getName(),
			'parameters' => $parameters,
		];
	}

}
