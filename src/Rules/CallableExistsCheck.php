<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Class_;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\ThisType;
use ReflectionClass;

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
	 * @param string $messagePrefix
	 * @return string[]
	 */
	public function checkCallableArgument(
		Arg $argument,
		Scope $scope,
		string $messagePrefix
	): array
	{
		$targetType = null;
		$targetMethod = null;
		$targetIsStaticMethod = false;
		$targetIsFunction = false;

		$callbackValue = $this->resolveValue($argument->value, $scope);

		if (is_string($callbackValue)) {
			$targetMethod = $callbackValue;
			$targetIsFunction = true;
		} elseif ($callbackValue instanceof MixedType || $callbackValue instanceof StringType) {
			return [];
		} elseif ($callbackValue instanceof ArrayType) {
			if (!$argument->value instanceof Array_) {
				return [];
			}
			$arrayItemsCount = count($argument->value->items);
			if ($arrayItemsCount < 2) {
				return [$messagePrefix . "array doesn't have required 2 items."];
			}
			if ($arrayItemsCount > 2) {
				return [$messagePrefix . 'array has too many items, only two items expected.'];
			}

			$item1 = $this->resolveValue($argument->value->items[0]->value, $scope);
			$item2 = $this->resolveValue($argument->value->items[1]->value, $scope);

			if (is_string($item1)) {
				$targetType = new ObjectType($item1);
				$targetIsStaticMethod = true;
			} elseif ($item1 instanceof ObjectType || $item1 instanceof ThisType) {
				$targetType = $item1;
				$targetIsStaticMethod = false;
			} elseif ($item1 instanceof MixedType || $item1 instanceof StringType) {
				return [];
			} else {
				return [$messagePrefix . 'array is not valid callback.'];
			}

			if (is_string($item2)) {
				$targetMethod = $item2;
			} elseif ($item2 instanceof MixedType || $item2 instanceof StringType) {
				return [];
			} else {
				return [$messagePrefix . 'array is not valid callback.'];
			}

		} else {
			return [$messagePrefix . 'value is not valid callback.'];
		}

		if (!is_string($targetMethod)) {
			if ($targetType !== null) {
				return [$messagePrefix . 'callback name is not string but ' . gettype($targetMethod)];
			} else {
				return [];
			}
		}

		if ($targetType === null && strpos($targetMethod, '::') !== false) {
			$targetTypeName = substr($targetMethod, 0, strpos($targetMethod, '::'));
			$targetMethod = substr($targetMethod, strpos($targetMethod, '::') + 2);
			if ($targetTypeName === '' || $targetMethod === '') {
				return [$messagePrefix . 'string is not valid callback.'];
			}
			$targetType = new ObjectType($targetTypeName);
			$targetIsFunction = false;
			$targetIsStaticMethod = true;
		}

		if ($targetType !== null) {
			// method
			if (!$targetType->canCallMethods()) {
				return [$messagePrefix . sprintf('callback cannot call method %s() on %s', $targetMethod, $targetType->describe())];
			}
			if ($targetMethod !== null && !$targetType->hasMethod($targetMethod)) {
				return [$messagePrefix . sprintf('%smethod %s::%s does not exists.', ($targetIsStaticMethod ? 'static ' : ''), $targetType->describe(), $targetMethod)];
			}
			if ($targetMethod !== null && $targetType->getMethod($targetMethod, $scope)
				->isStatic() !== $targetIsStaticMethod
			) {
				return [$messagePrefix . sprintf('%s method %s::%s as %s.', $targetIsStaticMethod ? 'non-static' : 'static', $targetType->describe(), $targetMethod, $targetIsStaticMethod ? 'static' : 'non-static')];
			}
		} elseif ($targetMethod !== null && $targetIsFunction) {
			// function
			try {
				$this->broker->getFunction(new Name($targetMethod), $scope);
			} catch (\PHPStan\Broker\FunctionNotFoundException $e) {
				return [$messagePrefix . sprintf('function %s not found.', $targetMethod)];
			}
		}

		return [];
	}

  /**
   * @param Expr $expr
   * @param Scope $scope
   * @return \PHPStan\Type\Type|string
   */
	private function resolveValue(Expr $expr, Scope $scope)
	{
		if ($expr instanceof String_) {
			return $expr->value;
		} elseif ($expr instanceof Variable) {
			return $scope->getType($expr);
		} elseif ($expr instanceof PropertyFetch) {
			return $scope->getType($expr);
		} elseif ($expr instanceof StaticPropertyFetch) {
			return $scope->getType($expr);
		} elseif ($expr instanceof ConstFetch) {
			if (!defined($expr->name->toString())) {
				return new MixedType();
			}
			 return constant($expr->name->toString());
		} elseif ($expr instanceof Class_) {
			return $scope->getClassReflection()->getName();
		} elseif ($expr instanceof Expr\ClassConstFetch && $expr->class instanceof Name) {
			if (strtolower($expr->name) === 'class') {
				return $scope->resolveName($expr->class);
			} else {
				return (new ReflectionClass($scope->resolveName($expr->class)))->getConstant($expr->name);
			}
		} elseif ($expr instanceof Concat) {
			$leftOp = $this->resolveValue($expr->left, $scope);
			$rightOp = $this->resolveValue($expr->right, $scope);
			if (is_string($leftOp) && is_string($rightOp)) {
				return $leftOp . $rightOp;
			} else {
				return $scope->getType($expr);
			}
		} else {
			return $scope->getType($expr);
		}
	}

}
