<?php declare(strict_types = 1);

namespace PHPStan\Dependency;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ParametersAcceptorWithPhpDocs;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\ReflectionWithFilename;
use PHPStan\Type\ClosureType;

class DependencyResolver
{

	/** @var Broker */
	private $broker;

	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
	}

	/**
	 * @param \PhpParser\Node $node
	 * @param Scope $scope
	 * @return ReflectionWithFilename[]
	 */
	public function resolveDependencies(\PhpParser\Node $node, Scope $scope): array
	{
		$dependenciesReflections = [];

		if ($node instanceof \PhpParser\Node\Stmt\Class_) {
			if ($node->extends !== null) {
				$this->addClassToDependencies($node->extends->toString(), $dependenciesReflections);
			}
			foreach ($node->implements as $className) {
				$this->addClassToDependencies($className->toString(), $dependenciesReflections);
			}
		} elseif ($node instanceof \PhpParser\Node\Stmt\Interface_) {
			if ($node->extends !== null) {
				foreach ($node->extends as $className) {
					$this->addClassToDependencies($className->toString(), $dependenciesReflections);
				}
			}
		} elseif ($node instanceof ClassMethod) {
			if (!$scope->isInClass()) {
				throw new \PHPStan\ShouldNotHappenException();
			}
			$nativeMethod = $scope->getClassReflection()->getNativeMethod($node->name->name);
			if ($nativeMethod instanceof PhpMethodReflection) {
				/** @var \PHPStan\Reflection\ParametersAcceptorWithPhpDocs $parametersAcceptor */
				$parametersAcceptor = ParametersAcceptorSelector::selectSingle($nativeMethod->getVariants());

				$this->extractFromParametersAcceptor($parametersAcceptor, $dependenciesReflections);
			}
		} elseif ($node instanceof Function_) {
			$functionName = $node->name->name;
			if (isset($node->namespacedName)) {
				$functionName = (string) $node->namespacedName;
			}
			$functionNameName = new Name($functionName);
			if ($this->broker->hasCustomFunction($functionNameName, null)) {
				$functionReflection = $this->broker->getCustomFunction($functionNameName, null);

				/** @var \PHPStan\Reflection\ParametersAcceptorWithPhpDocs $parametersAcceptor */
				$parametersAcceptor = ParametersAcceptorSelector::selectSingle($functionReflection->getVariants());
				$this->extractFromParametersAcceptor($parametersAcceptor, $dependenciesReflections);
			}
		} elseif ($node instanceof Closure) {
			/** @var ClosureType $closureType */
			$closureType = $scope->getType($node);
			foreach ($closureType->getParameters() as $parameter) {
				$referencedClasses = $parameter->getType()->getReferencedClasses();
				foreach ($referencedClasses as $referencedClass) {
					$this->addClassToDependencies($referencedClass, $dependenciesReflections);
				}
			}

			$returnTypeReferencedClasses = $closureType->getReturnType()->getReferencedClasses();
			foreach ($returnTypeReferencedClasses as $referencedClass) {
				$this->addClassToDependencies($referencedClass, $dependenciesReflections);
			}
		} elseif ($node instanceof \PhpParser\Node\Expr\FuncCall) {
			$functionName = $node->name;
			if ($functionName instanceof \PhpParser\Node\Name) {
				try {
					$dependenciesReflections[] = $this->getFunctionReflection($functionName, $scope);
				} catch (\PHPStan\Broker\FunctionNotFoundException $e) {
					// pass
				}
			} else {
				$variants = $scope->getType($functionName)->getCallableParametersAcceptors($scope);
				foreach ($variants as $variant) {
					$referencedClasses = $variant->getReturnType()->getReferencedClasses();
					foreach ($referencedClasses as $referencedClass) {
						$this->addClassToDependencies($referencedClass, $dependenciesReflections);
					}
				}
			}

			$returnType = $scope->getType($node);
			foreach ($returnType->getReferencedClasses() as $referencedClass) {
				$this->addClassToDependencies($referencedClass, $dependenciesReflections);
			}
		} elseif ($node instanceof \PhpParser\Node\Expr\MethodCall || $node instanceof \PhpParser\Node\Expr\PropertyFetch) {
			$classNames = $scope->getType($node->var)->getReferencedClasses();
			foreach ($classNames as $className) {
				$this->addClassToDependencies($className, $dependenciesReflections);
			}

			$returnType = $scope->getType($node);
			foreach ($returnType->getReferencedClasses() as $referencedClass) {
				$this->addClassToDependencies($referencedClass, $dependenciesReflections);
			}
		} elseif (
			$node instanceof \PhpParser\Node\Expr\StaticCall
			|| $node instanceof \PhpParser\Node\Expr\ClassConstFetch
			|| $node instanceof \PhpParser\Node\Expr\StaticPropertyFetch
		) {
			if ($node->class instanceof \PhpParser\Node\Name) {
				$this->addClassToDependencies($scope->resolveName($node->class), $dependenciesReflections);
			} else {
				foreach ($scope->getType($node->class)->getReferencedClasses() as $referencedClass) {
					$this->addClassToDependencies($referencedClass, $dependenciesReflections);
				}
			}

			$returnType = $scope->getType($node);
			foreach ($returnType->getReferencedClasses() as $referencedClass) {
				$this->addClassToDependencies($referencedClass, $dependenciesReflections);
			}
		} elseif (
			$node instanceof \PhpParser\Node\Expr\New_
			&& $node->class instanceof \PhpParser\Node\Name
		) {
			$this->addClassToDependencies($scope->resolveName($node->class), $dependenciesReflections);
		} elseif ($node instanceof \PhpParser\Node\Stmt\TraitUse) {
			foreach ($node->traits as $traitName) {
				$this->addClassToDependencies($traitName->toString(), $dependenciesReflections);
			}
		} elseif ($node instanceof \PhpParser\Node\Expr\Instanceof_) {
			if ($node->class instanceof Name) {
				$this->addClassToDependencies($scope->resolveName($node->class), $dependenciesReflections);
			}
		} elseif ($node instanceof \PhpParser\Node\Stmt\Catch_) {
			foreach ($node->types as $type) {
				$this->addClassToDependencies($scope->resolveName($type), $dependenciesReflections);
			}
		} elseif ($node instanceof ArrayDimFetch && $node->dim !== null) {
			$varType = $scope->getType($node->var);
			$dimType = $scope->getType($node->dim);

			foreach ($varType->getOffsetValueType($dimType)->getReferencedClasses() as $referencedClass) {
				$this->addClassToDependencies($referencedClass, $dependenciesReflections);
			}
		} elseif ($node instanceof Foreach_) {
			$exprType = $scope->getType($node->expr);
			if ($node->keyVar !== null) {
				foreach ($exprType->getIterableKeyType()->getReferencedClasses() as $referencedClass) {
					$this->addClassToDependencies($referencedClass, $dependenciesReflections);
				}
			}

			foreach ($exprType->getIterableValueType()->getReferencedClasses() as $referencedClass) {
				$this->addClassToDependencies($referencedClass, $dependenciesReflections);
			}
		} elseif ($node instanceof Array_) {
			$arrayType = $scope->getType($node);
			if (!$arrayType->isCallable()->no()) {
				foreach ($arrayType->getCallableParametersAcceptors($scope) as $variant) {
					$referencedClasses = $variant->getReturnType()->getReferencedClasses();
					foreach ($referencedClasses as $referencedClass) {
						$this->addClassToDependencies($referencedClass, $dependenciesReflections);
					}
				}
			}
		}

		return $dependenciesReflections;
	}

	/**
	 * @param string $className
	 * @param ReflectionWithFilename[] $dependenciesReflections
	 */
	private function addClassToDependencies(string $className, array &$dependenciesReflections): void
	{
		try {
			$classReflection = $this->broker->getClass($className);
		} catch (\PHPStan\Broker\ClassNotFoundException $e) {
			return;
		}

		do {
			$dependenciesReflections[] = $classReflection;

			foreach ($classReflection->getInterfaces() as $interface) {
				$dependenciesReflections[] = $interface;
			}

			foreach ($classReflection->getTraits() as $trait) {
				$dependenciesReflections[] = $trait;
			}

			$classReflection = $classReflection->getParentClass();
		} while ($classReflection !== false);
	}

	private function getFunctionReflection(\PhpParser\Node\Name $nameNode, ?Scope $scope): ReflectionWithFilename
	{
		$reflection = $this->broker->getFunction($nameNode, $scope);
		if (!$reflection instanceof ReflectionWithFilename) {
			throw new \PHPStan\Broker\FunctionNotFoundException((string) $nameNode);
		}

		return $reflection;
	}

	/**
	 * @param ParametersAcceptorWithPhpDocs $parametersAcceptor
	 * @param ReflectionWithFilename[] $dependenciesReflections
	 */
	private function extractFromParametersAcceptor(
		ParametersAcceptorWithPhpDocs $parametersAcceptor,
		array &$dependenciesReflections
	): void
	{
		foreach ($parametersAcceptor->getParameters() as $parameter) {
			$referencedClasses = array_merge(
				$parameter->getNativeType()->getReferencedClasses(),
				$parameter->getPhpDocType()->getReferencedClasses()
			);

			foreach ($referencedClasses as $referencedClass) {
				$this->addClassToDependencies($referencedClass, $dependenciesReflections);
			}
		}

		$returnTypeReferencedClasses = array_merge(
			$parametersAcceptor->getNativeReturnType()->getReferencedClasses(),
			$parametersAcceptor->getPhpDocReturnType()->getReferencedClasses()
		);
		foreach ($returnTypeReferencedClasses as $referencedClass) {
			$this->addClassToDependencies($referencedClass, $dependenciesReflections);
		}
	}

}
