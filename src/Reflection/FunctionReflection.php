<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PhpParser\Node\Stmt\Function_;
use PHPStan\Cache\Cache;
use PHPStan\Parser\FunctionCallStatementFinder;
use PHPStan\Parser\Parser;
use PHPStan\Reflection\Php\DummyParameter;
use PHPStan\Reflection\Php\PhpParameterReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\TrueOrFalseBooleanType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypehintHelper;

class FunctionReflection implements ParametersAcceptorWithPhpDocs
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

	/** @var \PHPStan\Reflection\ParameterReflection[] */
	private $parameters;

	/** @var \PHPStan\Type\Type */
	private $returnType;

	/** @var \PHPStan\Type\Type */
	private $nativeReturnType;

	public function __construct(
		\ReflectionFunction $reflection,
		Parser $parser,
		FunctionCallStatementFinder $functionCallStatementFinder,
		Cache $cache,
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
					new IntegerType(),
					true
				);
			}
			if (
				$this->reflection->getName() === 'fputcsv'
				&& count($this->parameters) === 4
			) {
				$this->parameters[] = new DummyParameter(
					'escape_char',
					new StringType(),
					true
				);
			}
			if (
				$this->reflection->getName() === 'unpack'
				&& PHP_VERSION_ID >= 70101
			) {
				$this->parameters[2] = new DummyParameter(
					'offset',
					new IntegerType(),
					true
				);
			}
			if (
				$this->reflection->getName() === 'imagepng'
				&& count($this->parameters) === 2
			) {
				$this->parameters[] = new DummyParameter(
					'quality',
					new IntegerType(),
					true
				);
				$this->parameters[] = new DummyParameter(
					'filters',
					new IntegerType(),
					true
				);
			}

			if (
				$this->reflection->getName() === 'session_start'
				&& count($this->parameters) === 0
			) {
				$this->parameters[] = new DummyParameter(
					'options',
					new ArrayType(new MixedType(), new MixedType()),
					true
				);
			}

			if ($this->reflection->getName() === 'locale_get_display_language') {
				$this->parameters[1] = new DummyParameter(
					'in_locale',
					new StringType(),
					true
				);
			}

			if (
				$this->reflection->getName() === 'imagewebp'
				&& count($this->parameters) === 2
			) {
				$this->parameters[] = new DummyParameter(
					'quality',
					new IntegerType(),
					true
				);
			}

			if (
				$this->reflection->getName() === 'setproctitle'
				&& count($this->parameters) === 0
			) {
				$this->parameters[] = new DummyParameter(
					'title',
					new StringType(),
					false
				);
			}

			if (
				$this->reflection->getName() === 'get_class'
			) {
				$this->parameters = [
					new DummyParameter(
						'object',
						new ObjectWithoutClassType(),
						true
					),
				];
			}

			if (
				$this->reflection->getName() === 'mysqli_fetch_all'
				&& count($this->parameters) === 1
			) {
				$this->parameters[] = new DummyParameter(
					'resulttype',
					new IntegerType(),
					true
				);
			}

			if (
				$this->reflection->getName() === 'openssl_open'
				&& count($this->parameters) === 5
			) {
				$this->parameters[4] = new DummyParameter(
					'method',
					new StringType(),
					true
				);
				$this->parameters[5] = new DummyParameter(
					'iv',
					new StringType(),
					true
				);
			}

			if (
				$this->reflection->getName() === 'openssl_x509_parse'
			) {
				$this->parameters[1] = new DummyParameter(
					'shortnames',
					new TrueOrFalseBooleanType(),
					true
				);
			}

			if (
				$this->reflection->getName() === 'get_defined_functions'
				&& count($this->parameters) > 0
				&& !$this->parameters[0]->isOptional()
			) {
				// PHP bug #75799
				$this->parameters[0] = new DummyParameter(
					'exclude_disabled',
					new TrueOrFalseBooleanType(),
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
			$this->nativeReturnType = TypehintHelper::decideTypeFromReflection($this->reflection->getReturnType());
		}

		return $this->nativeReturnType;
	}

}
