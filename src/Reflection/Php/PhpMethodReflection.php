<?php

namespace PHPStan\Reflection\Php;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Parser\FunctionCallStatementFinder;
use PHPStan\Parser\Parser;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;

class PhpMethodReflection implements MethodReflection
{

	/** @var \PHPStan\Reflection\ClassReflection */
	private $declaringClass;

	/** @var \ReflectionMethod */
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
		ClassReflection $declaringClass,
		\ReflectionMethod $reflection,
		Parser $parser,
		FunctionCallStatementFinder $functionCallStatementFinder,
		bool $lookForFuncGetArgs
	)
	{
		$this->declaringClass = $declaringClass;
		$this->reflection = $reflection;
		$this->parser = $parser;
		$this->functionCallStatementFinder = $functionCallStatementFinder;
		$this->lookForFuncGetArgs = $lookForFuncGetArgs;
	}

	public function getDeclaringClass(): ClassReflection
	{
		return $this->declaringClass;
	}

	public function isStatic(): bool
	{
		return $this->reflection->isStatic();
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
				$this->reflection->getName() === '__construct'
				&& $this->declaringClass->getName() === 'ArrayObject'
				&& count($this->parameters) === 1
			) {
				// PHP bug #71077
				$this->parameters[] = new DummyOptionalParameter();
				$this->parameters[] = new DummyOptionalParameter();
			}
		}

		return $this->parameters;
	}

	public function isVariadic(): bool
	{
		$isNativelyVariadic = $this->reflection->isVariadic();
		if (!$isNativelyVariadic && $this->lookForFuncGetArgs && $this->declaringClass->getNativeReflection()->getFileName() !== false) {
			$nodes = $this->parser->parse($this->declaringClass->getNativeReflection()->getFileName());
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

			if ($node instanceof ClassMethod) {
				if ($node->getStmts() === null) {
					continue; // interface
				}
				$methodName = $node->name;
				if ($methodName === $this->reflection->getName()) {
					return $this->functionCallStatementFinder->findFunctionCallInStatements('func_get_args', $node->getStmts()) !== null;
				}
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

}
