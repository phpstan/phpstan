<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Php;

use PhpParser\Node\Stmt\Function_;
use PHPStan\Cache\Cache;
use PHPStan\Parser\FunctionCallStatementFinder;
use PHPStan\Parser\Parser;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\FunctionVariantWithPhpDocs;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ParametersAcceptorWithPhpDocs;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypehintHelper;

class PhpFunctionReflection implements FunctionReflection
{

	/** @var \ReflectionFunction */
	private $reflection;

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

	/** @var \PHPStan\Reflection\ParameterReflection[] */
	private $parameters;

	/** @var \PHPStan\Type\Type */
	private $returnType;

	/** @var \PHPStan\Type\Type */
	private $nativeReturnType;

	/** @var bool */
	private $isDeprecated;

	/**
	 * @param \ReflectionFunction $reflection
	 * @param Parser $parser
	 * @param FunctionCallStatementFinder $functionCallStatementFinder
	 * @param Cache $cache
	 * @param \PHPStan\Type\Type[] $phpDocParameterTypes
	 * @param null|Type $phpDocReturnType
	 * @param null|Type $phpDocThrowType
	 * @param bool $isDeprecated
	 */
	public function __construct(
		\ReflectionFunction $reflection,
		Parser $parser,
		FunctionCallStatementFinder $functionCallStatementFinder,
		Cache $cache,
		array $phpDocParameterTypes,
		?Type $phpDocReturnType,
		?Type $phpDocThrowType,
		bool $isDeprecated
	)
	{
		$this->reflection = $reflection;
		$this->parser = $parser;
		$this->functionCallStatementFinder = $functionCallStatementFinder;
		$this->cache = $cache;
		$this->phpDocParameterTypes = $phpDocParameterTypes;
		$this->phpDocReturnType = $phpDocReturnType;
		$this->phpDocThrowType = $phpDocThrowType;
		$this->isDeprecated = $isDeprecated;
	}

	public function getName(): string
	{
		return $this->reflection->getName();
	}

	/**
	 * @return ParametersAcceptorWithPhpDocs[]
	 */
	public function getVariants(): array
	{
		return [
			new FunctionVariantWithPhpDocs(
				$this->getParameters(),
				$this->isVariadic(),
				$this->getReturnType(),
				$this->getPhpDocReturnType(),
				$this->getNativeReturnType()
			),
		];
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
					isset($this->phpDocParameterTypes[$reflection->getName()]) ? $this->phpDocParameterTypes[$reflection->getName()] : null
				);
			}, $this->reflection->getParameters());
		}

		return $this->parameters;
	}

	private function isVariadic(): bool
	{
		$isNativelyVariadic = $this->reflection->isVariadic();
		if (!$isNativelyVariadic && $this->reflection->getFileName() !== false) {
			$key = sprintf('variadic-function-%s-v0', $this->reflection->getName());
			$cachedResult = $this->cache->load($key);
			if ($cachedResult === null) {
				$nodes = $this->parser->parseFile($this->reflection->getFileName());
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

			if ($node instanceof Function_) {
				$functionName = (string) $node->namespacedName;

				if ($functionName === $this->reflection->getName()) {
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

	private function getReturnType(): Type
	{
		if ($this->returnType === null) {
			if ($this->reflection->getName() === 'count') {
				return $this->returnType = new IntegerType();
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
				$phpDocReturnType
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
			$this->nativeReturnType = TypehintHelper::decideTypeFromReflection($this->reflection->getReturnType());
		}

		return $this->nativeReturnType;
	}

	public function isDeprecated(): bool
	{
		return $this->isDeprecated || $this->reflection->isDeprecated();
	}

	public function getThrowType(): ?Type
	{
		return $this->phpDocThrowType;
	}

}
