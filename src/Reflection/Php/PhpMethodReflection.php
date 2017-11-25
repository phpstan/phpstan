<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Broker\Broker;
use PHPStan\Cache\Cache;
use PHPStan\Parser\FunctionCallStatementFinder;
use PHPStan\Parser\Parser;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ParametersAcceptorWithPhpDocs;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypehintHelper;
use PHPStan\Type\VoidType;

class PhpMethodReflection implements MethodReflection, ParametersAcceptorWithPhpDocs
{

	/** @var \PHPStan\Reflection\ClassReflection */
	private $declaringClass;

	/** @var \ReflectionMethod */
	private $reflection;

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Parser\Parser */
	private $parser;

	/** @var \PHPStan\Parser\FunctionCallStatementFinder */
	private $functionCallStatementFinder;

	/** @var \PHPStan\Cache\Cache */
	private $cache;

	/** @var \PHPStan\Type\Type[] */
	private $phpDocParameterTypes;

	/** @var \PHPStan\Type\Type|null */
	private $phpDocReturnType;

	/** @var \PHPStan\Reflection\ParameterReflection[] */
	private $parameters;

	/** @var \PHPStan\Type\Type */
	private $returnType;

	/** @var \PHPStan\Type\Type */
	private $nativeReturnType;

	public function __construct(
		ClassReflection $declaringClass,
		\ReflectionMethod $reflection,
		Broker $broker,
		Parser $parser,
		FunctionCallStatementFinder $functionCallStatementFinder,
		Cache $cache,
		array $phpDocParameterTypes,
		Type $phpDocReturnType = null
	)
	{
		$this->declaringClass = $declaringClass;
		$this->reflection = $reflection;
		$this->broker = $broker;
		$this->parser = $parser;
		$this->functionCallStatementFinder = $functionCallStatementFinder;
		$this->cache = $cache;
		$this->phpDocParameterTypes = $phpDocParameterTypes;
		$this->phpDocReturnType = $phpDocReturnType;
	}

	public function getDeclaringClass(): ClassReflection
	{
		return $this->declaringClass;
	}

	/**
	 * @return string|false
	 */
	public function getDocComment()
	{
		return $this->reflection->getDocComment();
	}

	public function getPrototype(): MethodReflection
	{
		try {
			$prototypeReflection = $this->reflection->getPrototype();
			$prototypeDeclaringClass = $this->broker->getClassFromReflection(
				$prototypeReflection->getDeclaringClass(),
				$prototypeReflection->getDeclaringClass()->getName(),
				$prototypeReflection->getDeclaringClass()->isAnonymous()
			);

			return new self(
				$prototypeDeclaringClass,
				$prototypeReflection,
				$this->broker,
				$this->parser,
				$this->functionCallStatementFinder,
				$this->cache,
				$this->phpDocParameterTypes,
				$this->phpDocReturnType
			);
		} catch (\ReflectionException $e) {
			return $this;
		}
	}

	public function isStatic(): bool
	{
		return $this->reflection->isStatic();
	}

	public function getName(): string
	{
		$name = $this->reflection->getName();
		$lowercaseName = strtolower($name);
		if ($lowercaseName === $name) {
			// fix for https://bugs.php.net/bug.php?id=74939
			foreach ($this->getDeclaringClass()->getNativeReflection()->getTraitAliases() as $traitTarget) {
				$correctName = $this->getMethodNameWithCorrectCase($name, $traitTarget);
				if ($correctName !== null) {
					$name = $correctName;
					break;
				}
			}
		}

		return $name;
	}

	/**
	 * @param string $lowercaseMethodName
	 * @param string $traitTarget
	 * @return string|null
	 */
	private function getMethodNameWithCorrectCase(string $lowercaseMethodName, string $traitTarget)
	{
		list ($trait, $method) = explode('::', $traitTarget);
		$traitReflection = $this->broker->getClass($trait)->getNativeReflection();
		foreach ($traitReflection->getTraitAliases() as $methodAlias => $traitTarget) {
			if ($lowercaseMethodName === strtolower($methodAlias)) {
				return $methodAlias;
			}

			$correctName = $this->getMethodNameWithCorrectCase($lowercaseMethodName, $traitTarget);
			if ($correctName !== null) {
				return $correctName;
			}
		}

		return null;
	}

	/**
	 * @return \PHPStan\Reflection\ParameterReflection[]
	 */
	public function getParameters(): array
	{
		if ($this->parameters === null) {
			$this->parameters = array_map(function (\ReflectionParameter $reflection) {
				return new PhpParameterReflection(
					$reflection,
					isset($this->phpDocParameterTypes[$reflection->getName()]) ? $this->phpDocParameterTypes[$reflection->getName()] : null
				);
			}, $this->reflection->getParameters());

			if (
				$this->reflection->getName() === '__construct'
				&& $this->declaringClass->getName() === 'ArrayObject'
				&& count($this->parameters) === 1
			) {
				// PHP bug #71077
				$this->parameters[] = new DummyParameter(
					'flags',
					new IntegerType(),
					true
				);
				$this->parameters[] = new DummyParameter(
					'iterator_class',
					new StringType(),
					true
				);
			}

			if (
				$this->declaringClass->getName() === 'ReflectionMethod'
				&& $this->reflection->getName() === 'invoke'
				&& !$this->parameters[1]->isOptional()
			) {
				// PHP bug #71416
				$this->parameters[1] = new DummyParameter(
					'parameter',
					new MixedType(),
					true,
					false,
					true
				);
			}

			if (
				$this->declaringClass->getName() === 'PDO'
				&& $this->reflection->getName() === 'query'
				&& count($this->parameters) < 4
			) {
				$this->parameters[] = new DummyParameter(
					'statement',
					new StringType(),
					false
				);
				$this->parameters[] = new DummyParameter(
					'fetchColumn',
					new IntegerType(),
					true
				);
				$this->parameters[] = new DummyParameter(
					'colno',
					new MixedType(),
					true
				);
				$this->parameters[] = new DummyParameter(
					'constructorArgs',
					new ArrayType(new MixedType(), new MixedType(), false),
					true
				);
			}
			if (
				$this->declaringClass->getName() === 'DatePeriod'
				&& $this->reflection->getName() === '__construct'
				&& count($this->parameters) < 4
			) {
				$this->parameters[] = new DummyParameter(
					'options',
					new IntegerType(),
					true
				);
			}
			if (
				$this->declaringClass->getName() === 'Closure'
				&& $this->reflection->getName() === '__invoke'
				&& count($this->parameters) < 1
			) {
				$this->parameters[] = new DummyParameter(
					'args',
					new MixedType(),
					true,
					false,
					true
				);
			}
			if (
				$this->declaringClass->getName() === 'ReflectionClass'
				&& $this->reflection->getName() === 'newInstance'
				&& count($this->parameters) === 1
			) {
				$this->parameters[0] = new DummyParameter(
					'args',
					new MixedType(),
					true,
					false,
					true
				);
			}
			if (
				$this->declaringClass->getName() === 'DateTimeZone'
				&& $this->reflection->getName() === 'getTransitions'
				&& count($this->parameters) === 2
			) {
				$this->parameters[0] = new DummyParameter(
					'timestamp_begin',
					new IntegerType(),
					true
				);
				$this->parameters[1] = new DummyParameter(
					'timestamp_end',
					new IntegerType(),
					true
				);
			}

			if (
				$this->declaringClass->getName() === 'Locale'
				&& $this->reflection->getName() === 'getDisplayLanguage'
			) {
				$this->parameters[1] = new DummyParameter(
					'in_locale',
					new StringType(),
					true
				);
			}

			if (
				$this->declaringClass->getName() === 'DOMDocument'
				&& $this->reflection->getName() === 'saveHTML'
				&& count($this->parameters) === 0
			) {
				$this->parameters[] = new DummyParameter(
					'node',
					TypeCombinator::addNull(new ObjectType('DOMNode')),
					true
				);
			}
		}

		return $this->parameters;
	}

	public function isVariadic(): bool
	{
		$isNativelyVariadic = $this->reflection->isVariadic();
		if (
			!$isNativelyVariadic
			&& (
				(
					$this->declaringClass->getName() === 'ReflectionMethod'
					&& $this->reflection->getName() === 'invoke'
				)
				|| (
					$this->declaringClass->getName() === 'Closure'
					&& $this->reflection->getName() === '__invoke'
				)
				|| (
					$this->declaringClass->getName() === 'ReflectionClass'
					&& $this->reflection->getName() === 'newInstance'
				)
			)
		) {
			return true;
		}

		if (!$isNativelyVariadic && $this->declaringClass->getFileName() !== false) {
			$key = sprintf('variadic-method-%s-%s-v0', $this->declaringClass->getName(), $this->reflection->getName());
			$cachedResult = $this->cache->load($key);
			if ($cachedResult === null) {
				$nodes = $this->parser->parseFile($this->declaringClass->getFileName());
				$result = $this->callsFuncGetArgs($nodes);
				$this->cache->save($key, $result);
				return $result;
			}

			return $cachedResult;
		}

		return $isNativelyVariadic;
	}

	/**
	 * @param mixed $nodes
	 * @return bool
	 */
	private function callsFuncGetArgs($nodes): bool
	{
		foreach ($nodes as $node) {
			if (is_array($node)) {
				if ($this->callsFuncGetArgs($node)) {
					return true;
				}
			}

			if (!($node instanceof \PhpParser\Node)) {
				continue;
			}

			if (
				$node instanceof \PhpParser\Node\Stmt\ClassLike
				&& isset($node->namespacedName)
				&& $this->declaringClass->getName() !== (string) $node->namespacedName
			) {
				continue;
			}

			if ($node instanceof ClassMethod) {
				if ($node->getStmts() === null) {
					continue; // interface
				}

				$methodName = $node->name;
				if ($methodName === $this->reflection->getName()) {
					return $this->functionCallStatementFinder->findFunctionCallInStatements(ParametersAcceptor::VARIADIC_FUNCTIONS, $node->getStmts()) !== null;
				}

				continue;
			}

			if ($this->callsFuncGetArgs($node)) {
				return true;
			}
		}

		return false;
	}

	public function isPrivate(): bool
	{
		return $this->reflection->isPrivate();
	}

	public function isPublic(): bool
	{
		return $this->reflection->isPublic();
	}

	public function getReturnType(): Type
	{
		if ($this->returnType === null) {
			if ($this->getName() === '__construct') {
				return $this->returnType = new VoidType();
			}
			$returnType = $this->reflection->getReturnType();
			$phpDocReturnType = $this->phpDocReturnType;
			if (
				$returnType !== null
				&& $phpDocReturnType !== null
				&& $returnType->allowsNull() !== TypeCombinator::containsNull($phpDocReturnType)
			) {
				$phpDocReturnType = null;
			}
			$this->returnType = TypehintHelper::decideTypeFromReflection(
				$returnType,
				$phpDocReturnType,
				$this->declaringClass->getName()
			);
		}

		return $this->returnType;
	}

	public function getPhpDocReturnType(): Type
	{
		if ($this->phpDocReturnType !== null) {
			return $this->phpDocReturnType;
		}

		return new MixedType();
	}

	public function getNativeReturnType(): Type
	{
		if ($this->nativeReturnType === null) {
			$this->nativeReturnType = TypehintHelper::decideTypeFromReflection(
				$this->reflection->getReturnType(),
				null,
				$this->declaringClass->getName()
			);
		}

		return $this->nativeReturnType;
	}

}
