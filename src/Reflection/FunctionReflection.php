<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PhpParser\Node\Stmt\Function_;
use PHPStan\Parser\FunctionCallStatementFinder;
use PHPStan\Parser\Parser;
use PHPStan\Reflection\Php\DummyParameter;
use PHPStan\Reflection\Php\PhpParameterReflection;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypehintHelper;

class FunctionReflection implements ParametersAcceptor
{

	/** @var \ReflectionFunction */
	private $reflection;

	/** @var \PHPStan\Parser\Parser */
	private $parser;

	/** @var \PHPStan\Parser\FunctionCallStatementFinder */
	private $functionCallStatementFinder;

	/** @var \Nette\Caching\Cache */
	private $cache;

	/** @var \PHPStan\Type\Type[] */
	private $phpDocParameterTypes;

	/** @var \PHPStan\Type\Type */
	private $phpDocReturnType;

	/** @var \PHPStan\Reflection\ParameterReflection[] */
	private $parameters;

	/** @var \PHPStan\Type\Type */
	private $returnType;

	public function __construct(
		\ReflectionFunction $reflection,
		Parser $parser,
		FunctionCallStatementFinder $functionCallStatementFinder,
		\Nette\Caching\Cache $cache,
		array $phpDocParameterTypes,
		Type $phpDocReturnType = null
	)
	{
		$this->reflection = $reflection;
		$this->parser = $parser;
		$this->functionCallStatementFinder = $functionCallStatementFinder;
		$this->cache = $cache;
		$this->phpDocParameterTypes = $phpDocParameterTypes;
		$this->phpDocReturnType = $phpDocReturnType;
	}

	public function getNativeReflection(): \ReflectionFunction
	{
		return $this->reflection;
	}

	public function getName(): string
	{
		return $this->reflection->getName();
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
				$this->reflection->getName() === 'array_unique'
				&& count($this->parameters) === 1
			) {
				// PHP bug #70960
				$this->parameters[] = new DummyParameter(
					'sort_flags',
					new IntegerType(false),
					true
				);
			}
			if (
				$this->reflection->getName() === 'fputcsv'
				&& count($this->parameters) === 4
			) {
				$this->parameters[] = new DummyParameter(
					'escape_char',
					new StringType(false),
					true
				);
			}
			if (
				$this->reflection->getName() === 'unpack'
				&& PHP_VERSION_ID >= 70101
			) {
				$this->parameters[2] = new DummyParameter(
					'offset',
					new IntegerType(false),
					true
				);
			}
			if (
				$this->reflection->getName() === 'imagepng'
				&& count($this->parameters) === 2
			) {
				$this->parameters[] = new DummyParameter(
					'quality',
					new IntegerType(false),
					true
				);
				$this->parameters[] = new DummyParameter(
					'filters',
					new IntegerType(false),
					true
				);
			}
		}

		return $this->parameters;
	}

	public function isVariadic(): bool
	{
		$isNativelyVariadic = $this->reflection->isVariadic();
		if (!$isNativelyVariadic && $this->reflection->getFileName() !== false) {
			$key = sprintf('variadic-function-%s-v2', $this->reflection->getName());
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
				$functionName = $node->name;
				if ((string) $node->namespacedName) {
					$functionName = (string) $node->namespacedName;
				}

				if ($functionName === $this->reflection->getName()) {
					return $this->functionCallStatementFinder->findFunctionCallInStatements(self::VARIADIC_FUNCTIONS, $node->getStmts()) !== null;
				}

				continue;
			}

			if ($this->callsFuncGetArgs($node)) {
				return true;
			}
		}

		return false;
	}

	public function getReturnType(): Type
	{
		if ($this->returnType === null) {
			$returnType = $this->reflection->getReturnType();
			$phpDocReturnType = $this->phpDocReturnType;
			if (
				$returnType !== null
				&& $phpDocReturnType !== null
				&& $returnType->allowsNull() !== $phpDocReturnType->isNullable()
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

}
