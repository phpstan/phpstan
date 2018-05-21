<?php declare(strict_types = 1);

namespace PHPStan\Dependency;

use PHPStan\Analyser\Scope;
use PHPStan\File\FileHelper;

class DependencyResolver
{

	/** @var \PHPStan\File\FileHelper */
	private $fileHelper;

	public function __construct(FileHelper $fileHelper)
	{
		$this->fileHelper = $fileHelper;
	}

	public function resolveDependencies(\PhpParser\Node $node, Scope $scope): array
	{
		$dependenciesReflections = [];

		$addDeepDependencies = function (\ReflectionClass $classReflection) use (&$dependenciesReflections) {
			do {
				$dependenciesReflections[] = $classReflection;

				foreach ($classReflection->getInterfaces() as $interface) {
					$dependenciesReflections[] = $interface;
				}

				$classReflection = $classReflection->getParentClass();
			} while ($classReflection !== false);
		};

		if ($node instanceof \PhpParser\Node\Stmt\Class_) {
			if ($node->extends !== null) {
				$addDeepDependencies($this->getClassReflection($node->extends->toString()));
			}
			foreach ($node->implements as $className) {
				$addDeepDependencies($this->getClassReflection($className->toString()));
			}
		} elseif ($node instanceof \PhpParser\Node\Stmt\Interface_) {
			if ($node->extends !== null) {
				foreach ($node->extends as $className) {
					$addDeepDependencies($this->getClassReflection($className->toString()));
				}
			}
		} elseif ($node instanceof \PhpParser\Node\Param) {
			/** @var \PHPStan\Reflection\MethodReflection|\PHPStan\Reflection\FunctionReflection $function */
			$function = $scope->getFunction();
			foreach ($function->getParameters() as $parameterReflection) {
				foreach ($parameterReflection->getType()->getReferencedClasses() as $referencedClass) {
					$dependenciesReflections[] = $this->getClassReflection($referencedClass);
				}
			}
		} elseif ($node instanceof \PhpParser\Node\Stmt\Return_) {
			/** @var \PHPStan\Reflection\MethodReflection|\PHPStan\Reflection\FunctionReflection $function */
			$function = $scope->getFunction();
			foreach ($function->getReturnType()->getReferencedClasses() as $referencedClass) {
				$dependenciesReflections[] = $this->getClassReflection($referencedClass);
			}
		} elseif ($node instanceof \PhpParser\Node\Expr\FuncCall) {
			$functionName = $node->name;
			if ($functionName instanceof \PhpParser\Node\Name) {
				$functionName = $functionName->getAttribute('namespacedName');
			}
			if ($functionName instanceof \PhpParser\Node\Name && $functionName->isFullyQualified()) {
				$dependenciesReflections[] = $this->getFunctionReflection($functionName->toString());
			}
		} elseif ($node instanceof \PhpParser\Node\Expr\MethodCall || $node instanceof \PhpParser\Node\Expr\PropertyFetch) {
			$classNames = $scope->getType($node->var)->getReferencedClasses();
			foreach ($classNames as $className) {
				$addDeepDependencies($this->getClassReflection($className));
			}
		} elseif (
			$node instanceof \PhpParser\Node\Expr\StaticCall
			|| $node instanceof \PhpParser\Node\Expr\ClassConstFetch
			|| $node instanceof \PhpParser\Node\Expr\StaticPropertyFetch
			|| $node instanceof \PhpParser\Node\Expr\New_
		) {
			if ($node->class instanceof \PhpParser\Node\Name && $node->class->isFullyQualified()) {
				$addDeepDependencies($this->getClassReflection($scope->resolveName($node->class)));
			}
		} elseif ($node instanceof \PhpParser\Node\Stmt\TraitUse) {
			foreach ($node->traits as $traitName) {
				$addDeepDependencies($this->getClassReflection($traitName->toString()));
			}
		} elseif ($node instanceof \PhpParser\Node\Expr\Instanceof_) {
			if ($node->class instanceof \PhpParser\Node\Name && $node->class->isFullyQualified()) {
				$dependenciesReflections[] = $this->getClassReflection($scope->resolveName($node->class));
			}
		} elseif ($node instanceof \PhpParser\Node\Stmt\Catch_) {
			foreach ($node->types as $type) {
				if ($type instanceof \PhpParser\Node\Name && $type->isFullyQualified()) {
					$dependenciesReflections[] = $this->getClassReflection($scope->resolveName($type));
				}
			}
		}

		$dependencies = [];
		foreach ($dependenciesReflections as $dependencyReflection) {
			if ($dependencyReflection->isInternal()) {
				continue;
			}

			$dependencyFile = $dependencyReflection->getFileName();
			$dependencyFile = preg_replace('~\(\d+\) : eval\(\)\'d code$~', '', $dependencyFile);
			$dependencyFile = $this->fileHelper->normalizePath($dependencyFile);

			if ($scope->getFile() === $dependencyFile) {
				continue;
			}

			$dependencies[$dependencyFile] = $dependencyFile;
		}

		return array_values($dependencies);
	}

	private function getClassReflection(string $className): \ReflectionClass
	{
		return new \ReflectionClass($className);
	}

	private function getFunctionReflection(string $functionName): \ReflectionFunction
	{
		try {
			return new \ReflectionFunction($functionName);
		} catch (\ReflectionException $e) {
			$functionNameParts = explode('\\', ltrim($functionName, '\\'));
			if (count($functionNameParts) > 1) {
				return new \ReflectionFunction($functionNameParts[count($functionNameParts) - 1]);
			} else {
				throw $e;
			}
		}
	}

}
