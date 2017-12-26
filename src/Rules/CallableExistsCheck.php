<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Class_;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Type\ObjectType;

class CallableExistsCheck
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
	}

	/**
	 * @param \PhpParser\Node\Arg $argument
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param string $msgPrefix
	 * @return string[]
	 */
	public function checkCallableArgument(
		Arg $argument,
		Scope $scope,
		string $msgPrefix
	): array
	{
		$targetType = null;
		$targetValue = null;
		$targetMethod = null;
		$targetIsStaticMethod = false;
		if ($argument->value instanceof Array_) {
			$arrayItemsCount = count($argument->value->items);
			if ($arrayItemsCount < 2) {
				return [$msgPrefix . "array doesn't have required 2 items."];
			}
			if ($arrayItemsCount > 2) {
				return [$msgPrefix . 'array has too much items, only two items expected.'];
			}
			$item1 = $argument->value->items[0]->value;
			$item2 = $argument->value->items[1]->value;

			if ($item1 instanceof String_) {
				$targetType = new ObjectType($item1->value);
				$targetValue = $item1->value;
				$targetIsStaticMethod = true;
			} elseif ($item1 instanceof Variable) {
				$targetType = $scope->getType($item1);
				$targetIsStaticMethod = false;
			} elseif ($item1 instanceof ConstFetch) {
				$targetType = new ObjectType(constant($item1->name->toString()));
				$targetValue = constant($item1->name->toString());
				$targetIsStaticMethod = true;
			} elseif ($item1 instanceof Class_) {
				$targetType = new ObjectType($scope->getClassReflection()->getName());
				$targetIsStaticMethod = true;
			}

			if ($item2 instanceof String_) {
				$targetMethod = $item2->value;
			} elseif ($item2 instanceof ConstFetch) {
				$targetMethod = constant($item2->name->toString());
			} else {
				return [];
			}
		} elseif ($argument->value instanceof String_) {
			$targetMethod = $argument->value->value;
		} elseif ($argument->value instanceof ConstFetch) {
			$targetMethod = constant($argument->value->name->toString());
		}

		if (!is_string($targetMethod)) {
			if ($targetMethod !== null) {
				return [$msgPrefix . 'callback name is not string but ' . gettype($targetMethod)];
			} else {
				return [];
			}
		}

		if ($targetType === null && strpos($targetMethod, '::') !== false) {
			$targetType = new ObjectType(substr($targetMethod, 0, strpos($targetMethod, '::')));
			$targetMethod = substr($targetMethod, strpos($targetMethod, '::') + 2);
			$targetIsStaticMethod = true;
		}

		if ($targetType !== null) {
			// method
			if (!$targetType->canCallMethods()) {
				return [$msgPrefix . sprintf('array first item {%s}(%s) is not object with callable methods.', $targetType->describe(), $targetValue)];
			}
			if ($targetMethod !== null && !$targetType->hasMethod($targetMethod)) {
				return [$msgPrefix . sprintf('%smethod %s::%s does not exists.', ($targetIsStaticMethod ? 'static ' : ''), $targetType->describe(), $targetMethod)];
			}
			if ($targetMethod !== null && $targetType->getMethod($targetMethod, $scope)
				->isStatic() !== $targetIsStaticMethod
			) {
				return [$msgPrefix . sprintf('%s method %s::%s as %s.', $targetIsStaticMethod ? 'non-static' : 'static', $targetType->describe(), $targetMethod, $targetIsStaticMethod ? 'static' : 'non-static')];
			}
		} elseif ($targetMethod !== null) {
			// function
			try {
				$this->broker->getFunction(new Name($targetMethod), $scope);
			} catch (\PHPStan\Broker\FunctionNotFoundException $e) {
				return [$msgPrefix . sprintf('%s is not a existing function name.', $targetMethod)];
			}
		}

		return [];
	}

}
