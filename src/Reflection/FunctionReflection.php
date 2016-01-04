<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PhpParser\Node\Stmt\Function_;
use PHPStan\Parser\FunctionCallStatementFinder;
use PHPStan\Parser\Parser;
use PHPStan\Reflection\Php\DummyOptionalParameter;
use PHPStan\Reflection\Php\PhpParameterReflection;

class FunctionReflection implements ParametersAcceptor
{

	/** @var \ReflectionFunction */
	private $reflection;

	/** @var \PHPStan\Parser\Parser */
	private $parser;

	/** @var \PHPStan\Parser\FunctionCallStatementFinder */
	private $functionCallStatementFinder;

	/** @var bool */
	private $lookForFuncGetArgs;

	/** @var \PHPStan\Reflection\ParameterReflection[] */
	private $parameters;

	public function __construct(
		\ReflectionFunction $reflection,
		Parser $parser,
		FunctionCallStatementFinder $functionCallStatementFinder,
		bool $lookForFuncGetArgs
	)
	{
		$this->reflection = $reflection;
		$this->parser = $parser;
		$this->functionCallStatementFinder = $functionCallStatementFinder;
		$this->lookForFuncGetArgs = $lookForFuncGetArgs;
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
				$this->parameters[] = new DummyOptionalParameter();
			}
		}

		return $this->parameters;
	}

	public function isVariadic(): bool
	{
		$isNativelyVariadic = $this->reflection->isVariadic();
		if (!$isNativelyVariadic && $this->lookForFuncGetArgs && $this->reflection->getFileName() !== false) {
			$nodes = $this->parser->parse($this->reflection->getFileName());
			return $this->callsFuncGetArgs($nodes);
		}

		return $isNativelyVariadic;
	}

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

}
