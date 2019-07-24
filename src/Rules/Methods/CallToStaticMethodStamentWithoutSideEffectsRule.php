<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

class CallToStaticMethodStamentWithoutSideEffectsRule implements Rule
{

	/** @var \PHPStan\Rules\RuleLevelHelper */
	private $ruleLevelHelper;

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	public function __construct(
		RuleLevelHelper $ruleLevelHelper,
		Broker $broker
	)
	{
		$this->ruleLevelHelper = $ruleLevelHelper;
		$this->broker = $broker;
	}

	public function getNodeType(): string
	{
		return Node\Stmt\Expression::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Expression $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->expr instanceof Node\Expr\StaticCall) {
			return [];
		}

		$staticCall = $node->expr;
		if (!$staticCall->name instanceof Node\Identifier) {
			return [];
		}

		$methodName = $staticCall->name->toString();
		if ($staticCall->class instanceof Node\Name) {
			$className = $scope->resolveName($staticCall->class);
			if (!$this->broker->hasClass($className)) {
				return [];
			}

			$calledOnType = new ObjectType($className);
		} else {
			$typeResult = $this->ruleLevelHelper->findTypeToCheck(
				$scope,
				$staticCall->class,
				'',
				static function (Type $type) use ($methodName): bool {
					return $type->canCallMethods()->yes() && $type->hasMethod($methodName)->yes();
				}
			);
			$calledOnType = $typeResult->getType();
			if ($calledOnType instanceof ErrorType) {
				return [];
			}
		}

		if (!$calledOnType->canCallMethods()->yes()) {
			return [];
		}

		if (!$calledOnType->hasMethod($methodName)->yes()) {
			return [];
		}

		$method = $calledOnType->getMethod($methodName, $scope);
		if ($method->hasSideEffects()->no()) {
			return [
				sprintf(
					'Call to %s %s::%s() on a separate line has no effect.',
					$method->isStatic() ? 'static method' : 'method',
					$method->getDeclaringClass()->getDisplayName(),
					$method->getName()
				),
			];
		}

		return [];
	}

}
