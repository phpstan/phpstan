<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Broker\Broker;
use PHPStan\Cache\Cache;
use PHPStan\Parser\FunctionCallStatementFinder;
use PHPStan\Parser\Parser;
use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\DeprecatableReflection;
use PHPStan\Reflection\FinalizableReflection;
use PHPStan\Reflection\FunctionVariantWithPhpDocs;
use PHPStan\Reflection\InternableReflection;
use PHPStan\Reflection\MethodPrototypeReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ParametersAcceptorWithPhpDocs;
use PHPStan\Reflection\ThrowableReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypehintHelper;
use PHPStan\Type\VoidType;

class PhpMethodReflection implements MethodReflection, DeprecatableReflection, InternableReflection, FinalizableReflection, ThrowableReflection
{

	/** @var \PHPStan\Reflection\ClassReflection */
	private $declaringClass;

	/** @var ClassReflection|null */
	private $declaringTrait;

	/** @var BuiltinMethodReflection */
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

	/** @var \PHPStan\Type\Type|null */
	private $phpDocThrowType;

	/** @var \PHPStan\Reflection\ParameterReflection[]|null */
	private $parameters;

	/** @var \PHPStan\Type\Type|null */
	private $returnType;

	/** @var \PHPStan\Type\Type|null */
	private $nativeReturnType;

	/** @var bool */
	private $isDeprecated;

	/** @var bool */
	private $isInternal;

	/** @var bool */
	private $isFinal;

	/** @var FunctionVariantWithPhpDocs[]|null */
	private $variants;

	/**
	 * @param ClassReflection $declaringClass
	 * @param ClassReflection|null $declaringTrait
	 * @param BuiltinMethodReflection $reflection
	 * @param Broker $broker
	 * @param Parser $parser
	 * @param FunctionCallStatementFinder $functionCallStatementFinder
	 * @param Cache $cache
	 * @param \PHPStan\Type\Type[] $phpDocParameterTypes
	 * @param Type|null $phpDocReturnType
	 * @param Type|null $phpDocThrowType
	 * @param bool $isDeprecated
	 * @param bool $isInternal
	 * @param bool $isFinal
	 */
	public function __construct(
		ClassReflection $declaringClass,
		?ClassReflection $declaringTrait,
		BuiltinMethodReflection $reflection,
		Broker $broker,
		Parser $parser,
		FunctionCallStatementFinder $functionCallStatementFinder,
		Cache $cache,
		array $phpDocParameterTypes,
		?Type $phpDocReturnType,
		?Type $phpDocThrowType,
		bool $isDeprecated = false,
		bool $isInternal = false,
		bool $isFinal = false
	)
	{
		$this->declaringClass = $declaringClass;
		$this->declaringTrait = $declaringTrait;
		$this->reflection = $reflection;
		$this->broker = $broker;
		$this->parser = $parser;
		$this->functionCallStatementFinder = $functionCallStatementFinder;
		$this->cache = $cache;
		$this->phpDocParameterTypes = $phpDocParameterTypes;
		$this->phpDocReturnType = $phpDocReturnType;
		$this->phpDocThrowType = $phpDocThrowType;
		$this->isDeprecated = $isDeprecated;
		$this->isInternal = $isInternal;
		$this->isFinal = $isFinal;
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

	public function getPrototype(): ClassMemberReflection
	{
		try {
			$prototypeMethod = $this->reflection->getPrototype();
			$prototypeDeclaringClass = $this->broker->getClass($prototypeMethod->getDeclaringClass()->getName());

			return new MethodPrototypeReflection(
				$prototypeDeclaringClass,
				$prototypeMethod->isStatic(),
				$prototypeMethod->isPrivate(),
				$prototypeMethod->isPublic()
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

	private function getMethodNameWithCorrectCase(string $lowercaseMethodName, string $traitTarget): ?string
	{
		$trait = explode('::', $traitTarget)[0];
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
	 * @return ParametersAcceptorWithPhpDocs[]
	 */
	public function getVariants(): array
	{
		if ($this->variants === null) {
			$this->variants = [
				new FunctionVariantWithPhpDocs(
					$this->getParameters(),
					$this->isVariadic(),
					$this->getReturnType(),
					$this->getPhpDocReturnType(),
					$this->getNativeReturnType()
				),
			];
		}

		return $this->variants;
	}

	/**
	 * @return \PHPStan\Reflection\Php\PhpParameterReflection[]
	 */
	private function getParameters(): array
	{
		if ($this->parameters === null) {
			$this->parameters = array_map(function (\ReflectionParameter $reflection) {
				return new PhpParameterReflection(
					$reflection,
					$this->phpDocParameterTypes[$reflection->getName()] ?? null
				);
			}, $this->reflection->getParameters());
		}

		return $this->parameters;
	}

	private function isVariadic(): bool
	{
		$isNativelyVariadic = $this->reflection->isVariadic();
		$declaringClass = $this->declaringClass;
		$filename = $this->declaringClass->getFileName();
		if ($this->declaringTrait !== null) {
			$declaringClass = $this->declaringTrait;
			$filename = $this->declaringTrait->getFileName();
		}

		if (!$isNativelyVariadic && $filename !== false) {
			$key = sprintf('variadic-method-%s-%s-v1', $declaringClass->getName(), $this->reflection->getName());
			$cachedResult = $this->cache->load($key);
			if ($cachedResult === null) {
				$nodes = $this->parser->parseFile($filename);
				$result = $this->callsFuncGetArgs($declaringClass, $nodes);
				$this->cache->save($key, $result);
				return $result;
			}

			return $cachedResult;
		}

		return $isNativelyVariadic;
	}

	/**
	 * @param ClassReflection $declaringClass
	 * @param mixed $nodes
	 * @return bool
	 */
	private function callsFuncGetArgs(ClassReflection $declaringClass, $nodes): bool
	{
		foreach ($nodes as $node) {
			if (is_array($node)) {
				if ($this->callsFuncGetArgs($declaringClass, $node)) {
					return true;
				}
			}

			if (!($node instanceof \PhpParser\Node)) {
				continue;
			}

			if (
				$node instanceof \PhpParser\Node\Stmt\ClassLike
				&& isset($node->namespacedName)
				&& $declaringClass->getName() !== (string) $node->namespacedName
			) {
				continue;
			}

			if ($node instanceof ClassMethod) {
				if ($node->getStmts() === null) {
					continue; // interface
				}

				$methodName = $node->name->name;
				if ($methodName === $this->reflection->getName()) {
					return $this->functionCallStatementFinder->findFunctionCallInStatements(ParametersAcceptor::VARIADIC_FUNCTIONS, $node->getStmts()) !== null;
				}

				continue;
			}

			if ($this->callsFuncGetArgs($declaringClass, $node)) {
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

	private function getReturnType(): Type
	{
		if ($this->returnType === null) {
			$name = strtolower($this->getName());
			if (
				$name === '__construct'
				|| $name === '__destruct'
				|| $name === '__unset'
				|| $name === '__wakeup'
				|| $name === '__clone'
			) {
				return $this->returnType = new VoidType();
			}
			if ($name === '__tostring') {
				return $this->returnType = new StringType();
			}
			if ($name === '__isset') {
				return $this->returnType = new BooleanType();
			}
			if ($name === '__sleep') {
				return $this->returnType = new ArrayType(new IntegerType(), new StringType());
			}
			if ($name === '__set_state') {
				return $this->returnType = new ObjectWithoutClassType();
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

	private function getPhpDocReturnType(): Type
	{
		if ($this->phpDocReturnType !== null) {
			return $this->phpDocReturnType;
		}

		return new MixedType();
	}

	private function getNativeReturnType(): Type
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

	public function isDeprecated(): bool
	{
		return $this->isDeprecated;
	}

	public function isInternal(): bool
	{
		return $this->isInternal;
	}

	public function isFinal(): bool
	{
		return $this->isFinal;
	}

	public function getThrowType(): ?Type
	{
		return $this->phpDocThrowType;
	}

}
