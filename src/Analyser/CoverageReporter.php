<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\Broker\Broker;
use PHPStan\File\FileExcluder;
use PHPStan\File\FileHelper;
use PHPStan\Parser\Parser;

class CoverageReporter
{

	/**
	 * @var \PHPStan\Parser\Parser
	 */
	private $parser;

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @var \PHPStan\Analyser\NodeScopeResolver
	 */
	private $nodeScopeResolver;

	/**
	 * @var \PhpParser\PrettyPrinter\Standard
	 */
	private $printer;

	/**
	 * @var \PHPStan\Analyser\TypeSpecifier
	 */
	private $typeSpecifier;

	/**
	 * @var \PHPStan\File\FileExcluder
	 */
	private $fileExcluder;

	/**
	 * @var string|null
	 */
	private $bootstrapFile;

	/**
	 * @var bool
	 */
	private $reportUnmatchedIgnoredErrors;

	/**
	 * @param \PHPStan\Broker\Broker $broker
	 * @param \PHPStan\Parser\Parser $parser
	 * @param \PHPStan\Analyser\NodeScopeResolver $nodeScopeResolver
	 * @param \PhpParser\PrettyPrinter\Standard $printer
	 * @param \PHPStan\Analyser\TypeSpecifier $typeSpecifier
	 * @param \PHPStan\File\FileExcluder $fileExcluder
	 * @param \PHPStan\File\FileHelper $fileHelper
	 * @param string|null $bootstrapFile
	 */
	public function __construct(
		Broker $broker,
		Parser $parser,
		NodeScopeResolver $nodeScopeResolver,
		\PhpParser\PrettyPrinter\Standard $printer,
		TypeSpecifier $typeSpecifier,
		FileExcluder $fileExcluder,
		FileHelper $fileHelper,
		string $bootstrapFile = null
	)
	{
		$this->broker = $broker;
		$this->parser = $parser;
		$this->nodeScopeResolver = $nodeScopeResolver;
		$this->printer = $printer;
		$this->typeSpecifier = $typeSpecifier;
		$this->fileExcluder = $fileExcluder;
		$this->bootstrapFile = $bootstrapFile !== null ? $fileHelper->normalizePath($bootstrapFile) : null;
	}

	/**
	 * @param string[] $files
	 * @param \Closure|null $progressCallback
	 * @return array
	 */
	public function analyse(array $files, \Closure $progressCallback = null): array
	{
		if ($this->bootstrapFile !== null) {
			if (!is_file($this->bootstrapFile)) {
				throw new \RuntimeException('Bootstrap file %s does not exist.', $this->bootstrapFile);
			}

			require_once $this->bootstrapFile;
		}

		$reports = [];
		$this->nodeScopeResolver->setAnalysedFiles($files);
		foreach ($files as $file) {
			if ($this->fileExcluder->isExcludedFromAnalysing($file)) {
				if ($progressCallback !== null) {
					$progressCallback($file);
				}

				continue;
			}

			$typecheckWarnings = [];
			$this->nodeScopeResolver->processNodes(
				$this->parser->parseFile($file),
				new Scope($this->broker, $this->printer, $this->typeSpecifier, $file),
				function (\PhpParser\Node $node, Scope $scope) use (&$typecheckWarnings) {
					if ($node instanceof \PhpParser\Node\Stmt\Trait_) {
						return;
					}

					if ($node instanceof \PhpParser\Node\Expr\Variable) {
						$typecheckWarnings = array_merge(
							$typecheckWarnings,
							$this->handleVariableNode($node, $scope)
						);
					}

					if ($node instanceof \PhpParser\Node\Expr\PropertyFetch) {
						$typecheckWarnings = array_merge(
							$typecheckWarnings,
							$this->handlePropertyFetch($node, $scope)
						);
					}

					if ($node instanceof \PhpParser\Node\Expr\FuncCall) {
						$typecheckWarnings = array_merge(
							$typecheckWarnings,
							$this->handleFuncCall($node, $scope)
						);
					}

					if ($node instanceof \PhpParser\Node\Expr\MethodCall) {
						$typecheckWarnings = array_merge(
							$typecheckWarnings,
							$this->handleMethodCall($node, $scope)
						);
					}

					if ($node instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
						$typecheckWarnings = array_merge(
							$typecheckWarnings,
							$this->handleStaticPropertyFetch($node, $scope)
						);
					}

					if ($node instanceof \PhpParser\Node\Expr\StaticCall) {
						$typecheckWarnings = array_merge(
							$typecheckWarnings,
							$this->handleStaticMethodCall($node, $scope)
						);
					}
				}
			);
			if ($progressCallback !== null) {
				$progressCallback($file);
			}

			$report = [];
			foreach ($typecheckWarnings as $warning) {
				if (!isset($report[$warning['line']])) {
					$report[$warning['line']] = [];
				}

				$report[$warning['line']][] = $warning['reason'];
			}
			$reports[$file] = $report;
		}

		return $reports;
	}

	private function handleVariableNode($node, $scope)
	{
		// A useage, rather than assignment of the variable
		if (!$scope->isInVariableAssign($node->name)) {
			if (!$scope->hasVariableType($node->name)) {
				// Use of undefined variable, we don't have coverage
				// of this
				return [
					$this->buildFailure(
						$node,
						'Variable $' . $node->name . ' is not defined'
					)
				];
			} else {
				$type = $scope->getVariableType($node->name);
				if ($type instanceof \PHPStan\Type\MixedType) {
					return [
						$this->buildFailure(
							$node,
							'Variable $' . $node->name . ' does not have a type'
						)
					];
				}
			}
		}

		return [];
	}

	private function handlePropertyFetch($node, $scope)
	{
		$maybeClass = $this->getClass($node, $scope);
		if (is_null($maybeClass)) {
			return [];
		} else {
			$class = $maybeClass;
		}

		$name = $node->name;
		if (gettype($name) !== 'string') {
			// They're probably doing something like
			// $this->$someVar . We can't know the return
			// type of this usage.
			return [
				$this->buildFailure(
					$node,
					'Use of variable ' . $name . ' to access property of class ' . $class->getName()
				)
			];
		}
		if (!$class->hasProperty($name)) {
			return [];
		}

		$propertyReflection = $class->getProperty($name, $scope);
		$propType = $propertyReflection->getType();

		if ($propType instanceof \PHPStan\Type\MixedType) {
			return [
				$this->buildFailure(
					$node,
					'Property ' . $name . ' of class ' . $class->getName() . ' does not have a type'
				)
			];
		}

		return [];
	}

	private function handleStaticPropertyFetch($node, $scope)
	{
		if (!is_string($node->name) || !($node->class instanceof \PhpParser\Node\Name)) {
			return [];
		}

		$name = $node->name;
		$class = (string) $node->class;
		if ($class === 'self' || $class === 'static') {
			if (!$scope->isInClass()) {
				return [];
			}
			$classReflection = $scope->getClassReflection();
		} elseif ($class === 'parent') {
			if (!$scope->isInClass()) {
				return [];
			}
			if ($scope->getClassReflection()->getParentClass() === false) {
				return [];
			}

			if ($scope->getFunctionName() === null) {
				throw new \PHPStan\ShouldNotHappenException();
			}

			$classReflection = $scope->getClassReflection()->getParentClass();
		} else {
			if (!$this->broker->hasClass($class)) {
				return [
					$this->buildFailure(
						$node,
						'Can\'t find class ' . $class
					)
				];
			}
			$classReflection = $this->broker->getClass($class);
		}

		if (!$classReflection->hasProperty($name)) {
			return [
				$this->buildFailure(
					$node,
					'Static property ' . $name . ' does not exist on ' . $class
				)
			];
		}

		$property = $classReflection->getProperty($name, $scope);
		$propType = $property->getType();

		if ($propType instanceof \PHPStan\Type\MixedType) {
			return [
				$this->buildFailure(
					$node,
					'Static property ' . $name . ' of class ' . $class . ' does not have a type'
				)
			];
		}

		return [];
	}

	private function handleFuncCall($node, $scope)
	{
		if (!($node->name instanceof \PhpParser\Node\Name)) {
			return [];
		}

		if (!$this->broker->hasFunction($node->name, $scope)) {
			return [];
		}

		$function = $this->broker->getFunction($node->name, $scope);
		$type = $function->getReturnType();

		if ($type instanceof \PHPStan\Type\MixedType) {
			return [
				$this->buildFailure(
					$node,
					'Function ' . $node->name . ' does not have a defined return type'
				)
			];
		}

		return [];
	}

	private function handleMethodCall($node, $scope)
	{
		$maybeClass = $this->getClass($node, $scope);
		if (is_null($maybeClass)) {
			return [];
		} else {
			$class = $maybeClass;
		}

		$name = $node->name;
		if (!$class->hasMethod($name)) {
			return [];
		}

		$methodReflection = $class->getMethod($name, $scope);
		$type = $methodReflection->getReturnType();

		if ($type instanceof \PHPStan\Type\MixedType) {
			return [
				$this->buildFailure(
					$node,
					'Method ' . $name . ' does not have a defined return type'
				)
			];
		}

		return [];
	}

	private function handleStaticMethodCall($node, $scope)
	{
		if (!is_string($node->name) || !($node->class instanceof \PhpParser\Node\Name)) {
			return [];
		}

		$name = $node->name;
		$class = (string) $node->class;
		if ($class === 'self' || $class === 'static') {
			if (!$scope->isInClass()) {
				return [];
			}
			$classReflection = $scope->getClassReflection();
		} elseif ($class === 'parent') {
			if (!$scope->isInClass()) {
				return [];
			}
			if ($scope->getClassReflection()->getParentClass() === false) {
				return [];
			}

			if ($scope->getFunctionName() === null) {
				throw new \PHPStan\ShouldNotHappenException();
			}

			$classReflection = $scope->getClassReflection()->getParentClass();
		} else {
			if (!$this->broker->hasClass($class)) {
				return [
					$this->buildFailure(
						$node,
						'Can\'t find class ' . $class
					)
				];
			}
			$classReflection = $this->broker->getClass($class);
		}

		if (!$classReflection->hasMethod($name)) {
			return [
				$this->buildFailure(
					$node,
					'Static method ' . $name . ' does not exist on ' . $class
				)
			];
		}

		$method = $classReflection->getMethod($name, $scope);
		$type = $method->getReturnType();

		if ($type instanceof \PHPStan\Type\MixedType) {
			return [
				$this->buildFailure(
					$node,
					'Static method ' . $name . ' on class ' . $class . ' does not have a defined return type'
				)
			];
		}

		return [];
	}

	private function getClass($node, $scope)
	{
		$classType = $scope->getType($node->var);
		if (!$classType->canAccessProperties()) {
			return null;
		}

		$propertyClass = $classType->getClass();
		if ($propertyClass === null) {
			return null;
		}

		if (!$this->broker->hasClass($propertyClass)) {
			return null;
		}

		return $this->broker->getClass($propertyClass);
	}

	/**
	 * @param \PhpParser\Node $node
	 * @param string $reason
	 * @return array
	 */
	private function buildFailure($node, $reason)
	{
		return [
			'line' => $node->getLine(),
			'reason' => $reason
		];
	}
}
