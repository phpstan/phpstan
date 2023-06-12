<?php declare(strict_types = 1);

namespace IdentifierExtractor;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ObjectType;
use function array_map;
use function count;
use function sprintf;

/**
 * @implements Collector<MethodCall, array{identifiers: non-empty-list<string>, class: string, file: string, line: int}>
 */
class ErrorWithIdentifierCollector implements Collector
{

	public function getNodeType(): string
	{
		return MethodCall::class;
	}

	public function processNode(Node $node, Scope $scope)
	{
		if (!$node->name instanceof Node\Identifier) {
			return null;
		}

		if ($node->name->toLowerString() !== 'withidentifier') {
			return null;
		}

		$args = $node->getArgs();
		if (!isset($args[0])) {
			return null;
		}

		$identifier = $scope->getType($args[0]->value);
		if (count($identifier->getConstantStrings()) === 0) {
			throw new ShouldNotHappenException(sprintf('Unknown identifier'));
		}

		$calledOnType = $scope->getType($node->var);
		$error = new ObjectType(Error::class);
		if (!$error->isSuperTypeOf($calledOnType)->yes()) {
			return null;
		}

		if (!$scope->isInClass()) {
			return null;
		}

		return [
			'identifiers' => array_map(static fn ($type) => $type->getValue(), $identifier->getConstantStrings()),
			'class' => $scope->getClassReflection()->getName(),
			'file' => $scope->getFile(),
			'line' => $args[0]->getStartLine(),
		];
	}

}
