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
use PHPStan\Reflection\ReflectionWithFilename;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypehintHelper;

class PhpFunctionReflection implements FunctionReflection, ReflectionWithFilename
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

	/** @var bool */
	private $isDeprecated;

	/** @var bool */
	private $isInternal;

	/** @var bool */
	private $isFinal;

	/** @var string|false */
	private $filename;

	/** @var FunctionVariantWithPhpDocs[]|null */
	private $variants;

	/**
	 * @param \ReflectionFunction $reflection
	 * @param Parser $parser
	 * @param FunctionCallStatementFinder $functionCallStatementFinder
	 * @param Cache $cache
	 * @param \PHPStan\Type\Type[] $phpDocParameterTypes
	 * @param Type|null $phpDocReturnType
	 * @param Type|null $phpDocThrowType
	 * @param bool $isDeprecated
	 * @param bool $isInternal
	 * @param bool $isFinal
	 * @param string|false $filename
	 */
	public function __construct(
		\ReflectionFunction $reflection,
		Parser $parser,
		FunctionCallStatementFinder $functionCallStatementFinder,
		Cache $cache,
		array $phpDocParameterTypes,
		?Type $phpDocReturnType,
		?Type $phpDocThrowType,
		bool $isDeprecated,
		bool $isInternal,
		bool $isFinal,
		$filename
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
		$this->isInternal = $isInternal;
		$this->isFinal = $isFinal;
		$this->filename = $filename;
	}

	public function getName(): string
	{
		return $this->reflection->getName();
	}

	/**
	 * @return string|false
	 */
	public function getFileName()
	{
		return $this->filename;
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
		return array_map(function (\ReflectionParameter $reflection) {
			return new PhpParameterReflection(
				$reflection,
				$this->phpDocParameterTypes[$reflection->getName()] ?? null
			);
		}, $this->reflection->getParameters());
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
		if ($this->reflection->getName() === 'count') {
			return new IntegerType();
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
		return TypehintHelper::decideTypeFromReflection(
			$returnType,
			$phpDocReturnType
		);
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
		return TypehintHelper::decideTypeFromReflection($this->reflection->getReturnType());
	}

	public function isDeprecated(): bool
	{
		return $this->isDeprecated || $this->reflection->isDeprecated();
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
