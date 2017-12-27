<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
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

		if ($argument->value instanceof Array_) {
			$arrayItemsCount = count($argument->value->items);
			if ($arrayItemsCount < 2) {
				return [$messagePrefix . "array doesn't have required 2 items."];
			}
			if ($arrayItemsCount > 2) {
				return [$messagePrefix . 'array has too many items, only two items expected.'];
			}
			$item1 = $argument->value->items[0]->value;
			$item2 = $argument->value->items[1]->value;

			if ($item1 instanceof String_) {
				$targetType = new ObjectType($item1->value);
				$targetIsStaticMethod = true;
			} elseif ($item1 instanceof Variable) {
				$targetType = $scope->getType($item1);
				$targetIsStaticMethod = false;
			} elseif ($item1 instanceof PropertyFetch) {
				$targetType = $scope->getType($item1);
				$targetIsStaticMethod = false;
			} elseif ($item1 instanceof StaticPropertyFetch) {
				$targetType = $scope->getType($item1);
				$targetIsStaticMethod = false;
			} elseif ($item1 instanceof ConstFetch) {
				if (!defined($item1->name->toString())) {
					return [];
				}
				$targetType = new ObjectType(constant($item1->name->toString()));
				$targetIsStaticMethod = true;
			} elseif ($item1 instanceof Class_) {
				$targetType = new ObjectType($scope->getClassReflection()->getName());
				$targetIsStaticMethod = true;
			} elseif ($item1 instanceof Expr\ClassConstFetch && $item1->class instanceof Name) {
				$targetType = new ObjectType($scope->resolveName($item1->class));
				$targetIsStaticMethod = true;
			} elseif ($item1 instanceof Expr) {
				$targetType = $scope->getType($item1);
				if ($targetType instanceof ObjectType) {
					$targetIsStaticMethod = false;
				} elseif ($targetType instanceof StringType) {
					$targetIsStaticMethod = true;
				} else {
					return [$messagePrefix . 'array is not valid callback.'];
				}
			} else {
				return [];
			}

			if ($targetType instanceof MixedType || $targetType instanceof StringType) {
				return [];
			}

			if ($item2 instanceof String_) {
				$targetMethod = $item2->value;
			} elseif ($item2 instanceof ConstFetch) {
				if (!defined($item2->name->toString())) {
					return [];
				}
				$targetMethod = constant($item2->name->toString());
			} elseif ($item2 instanceof Expr) {
				$argType = $scope->getType($item2);
				if ($argType instanceof StringType) {
					return [];
				} else {
					return [$messagePrefix . 'array is not valid callback.'];
				}
			} else {
				return [];
			}

		} elseif ($argument->value instanceof String_) {
			$targetMethod = $argument->value->value;
			$targetIsFunction = true;
		} elseif ($argument->value instanceof ConstFetch) {
			if (!defined($argument->value->name->toString())) {
				return [];
			}
			$targetMethod = constant($argument->value->name->toString());
			$targetIsFunction = true;
		} elseif ($argument->value instanceof Expr) {
			$targetType = $scope->getType($argument->value);
			$targetIsFunction = true;
			if ($targetType instanceof StringType) {
				return [];
			} elseif ($targetType instanceof ArrayType) {
					return [];
			} else {
					return [$messagePrefix . 'value is not valid callback.'];
			}
		}

		if (!is_string($targetMethod)) {
			if ($targetMethod !== null) {
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

}
