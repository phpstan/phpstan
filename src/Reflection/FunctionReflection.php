<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PhpParser\Node\Stmt\Function_;
use PHPStan\Parser\FunctionCallStatementFinder;
use PHPStan\Parser\Parser;
use PHPStan\Reflection\Php\DummyOptionalParameter;
use PHPStan\Reflection\Php\PhpParameterReflection;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
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

	/** @var \PHPStan\Reflection\ParameterReflection[] */
	private $parameters;

	/** @var \PHPStan\Type\Type */
	private $returnType;

	public function __construct(
		\ReflectionFunction $reflection,
		Parser $parser,
		FunctionCallStatementFinder $functionCallStatementFinder,
		\Nette\Caching\Cache $cache
	)
	{
		$this->reflection = $reflection;
		$this->parser = $parser;
		$this->functionCallStatementFinder = $functionCallStatementFinder;
		$this->cache = $cache;
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
				return new PhpParameterReflection($reflection);
			}, $this->reflection->getParameters());
			if (
				$this->reflection->getName() === 'array_unique'
				&& count($this->parameters) === 1
			) {
				// PHP bug #70960
				$this->parameters[] = new DummyOptionalParameter(
					'sort_flags',
					new IntegerType(false)
				);
			}
		}

		return $this->parameters;
	}

	public function isVariadic(): bool
	{
		$isNativelyVariadic = $this->reflection->isVariadic();
		if (!$isNativelyVariadic && $this->reflection->getFileName() !== false) {
			$key = sprintf('variadic-function-%s', $this->reflection->getName());
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
					return $this->functionCallStatementFinder->findFunctionCallInStatements('func_get_args', $node->getStmts()) !== null;
				}
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
			$phpTypeReflection = $this->reflection->getReturnType();
			if ($phpTypeReflection === null) {
				$this->returnType = new MixedType(true);
			} else {
				$this->returnType = TypehintHelper::getTypeObjectFromTypehint(
					(string) $phpTypeReflection,
					$phpTypeReflection->allowsNull()
				);
			}
		}

		return $this->returnType;
	}

}
