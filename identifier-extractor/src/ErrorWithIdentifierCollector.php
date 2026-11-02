<?php declare(strict_types = 1);

namespace IdentifierExtractor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\ObjectType;
use function array_map;
use function count;
use function sprintf;
use function str_starts_with;

/**
 * Collects identifiers of errors created directly as Error objects:
 * `$error->withIdentifier('foo.bar')` and `new Error(..., identifier: 'foo.bar')`.
 *
 * @implements Collector<Expr, array{identifiers: non-empty-list<string>, class: string, file: string, line: int}>
 */
class ErrorWithIdentifierCollector implements Collector
{

	private const ERROR_CONSTRUCTOR_IDENTIFIER_POSITION = 9;

	public function getNodeType(): string
	{
		return Expr::class;
	}

	public function processNode(Node $node, Scope $scope)
	{
		if (!$scope->isInClass()) {
			return null;
		}

		$className = $scope->getClassReflection()->getName();

		if ($node instanceof MethodCall) {
			$identifierArg = $this->getWithIdentifierArg($node, $scope);
			if ($identifierArg === null) {
				return null;
			}

			$identifier = $scope->getType($identifierArg->value);
			if (count($identifier->getConstantStrings()) === 0) {
				throw new ShouldNotHappenException(sprintf('Unknown identifier'));
			}
		} elseif ($node instanceof New_) {
			// errors created in test helpers are not reported to users
			if (str_starts_with($className, 'PHPStan\\Testing\\')) {
				return null;
			}

			$identifierArg = $this->getNewErrorIdentifierArg($node, $scope);
			if ($identifierArg === null) {
				return null;
			}

			// a variable identifier passes on an identifier collected elsewhere, like in RuleErrorTransformer
			$identifier = $scope->getType($identifierArg->value);
			if (count($identifier->getConstantStrings()) === 0) {
				return null;
			}
		} else {
			return null;
		}

		return [
			'identifiers' => array_map(static fn ($type) => $type->getValue(), $identifier->getConstantStrings()),
			'class' => $className,
			'file' => $scope->getFile(),
			'line' => $identifierArg->getStartLine(),
		];
	}

	private function getWithIdentifierArg(MethodCall $node, Scope $scope): ?Node\Arg
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

		$calledOnType = $scope->getType($node->var);
		$error = new ObjectType(Error::class);
		if (!$error->isSuperTypeOf($calledOnType)->yes()) {
			return null;
		}

		return $args[0];
	}

	private function getNewErrorIdentifierArg(New_ $node, Scope $scope): ?Node\Arg
	{
		if (!$node->class instanceof Node\Name) {
			return null;
		}

		if ($scope->resolveName($node->class) !== Error::class) {
			return null;
		}

		foreach ($node->getArgs() as $i => $arg) {
			if ($arg->name === null) {
				if ($i === self::ERROR_CONSTRUCTOR_IDENTIFIER_POSITION) {
					return $arg;
				}

				continue;
			}

			if ($arg->name->toString() === 'identifier') {
				return $arg;
			}
		}

		return null;
	}

}
