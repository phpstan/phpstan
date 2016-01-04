<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\Error;
use PHPStan\Broker\Broker;
use PHPStan\Parser\DirectParser;
use PHPStan\Parser\FunctionCallStatementFinder;
use PHPStan\Parser\Parser;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\FunctionReflectionFactory;
use PHPStan\Reflection\Php\PhpClassReflectionExtension;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\Php\PhpMethodReflectionFactory;

abstract class AbstractRuleTest extends \PHPStan\TestCase
{

	/** @var \PHPStan\Analyser\Analyser */
	private $analyser;

	/** @var \PHPStan\Broker\Broker  */
	private $broker;

	/** @var \PHPStan\Parser\Parser */
	private $parser;

	abstract protected function getRule(): Rule;

	/**
	 * @return \PHPStan\Analyser\Analyser
	 */
	private function getAnalyser(): Analyser
	{
		if ($this->analyser === null) {
			$registry = new Registry();
			$registry->register($this->getRule());

			$this->analyser = new Analyser(
				$this->getParser(),
				$registry,
				[]
			);
		}

		return $this->analyser;
	}

	private function getParser(): \PHPStan\Parser\Parser
	{
		if ($this->parser === null) {
			$traverser = new \PhpParser\NodeTraverser();
			$traverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
			$this->parser = new DirectParser(new \PhpParser\Parser\Php7(new \PhpParser\Lexer()), $traverser);
		}

		return $this->parser;
	}

	/**
	 * @param \PHPStan\Reflection\ClassReflectionExtension[] $extensions
	 * @return \PHPStan\Broker\Broker
	 */
	public function getBroker(array $extensions = []): Broker
	{
		if ($this->broker === null) {
			$functionCallStatementFinder = new FunctionCallStatementFinder();
			$phpExtension = new PhpClassReflectionExtension(new class($this->getParser(), $functionCallStatementFinder) implements PhpMethodReflectionFactory {
				/** @var \PHPStan\Parser\Parser */
			private $parser;

				/** @var \PHPStan\Parser\FunctionCallStatementFinder */
			private $functionCallStatementFinder;

			public function __construct(Parser $parser, FunctionCallStatementFinder $functionCallStatementFinder)
			{
				$this->parser = $parser;
				$this->functionCallStatementFinder = $functionCallStatementFinder;
			}

			public function create(
				ClassReflection $declaringClass,
				\ReflectionMethod $reflection
			): PhpMethodReflection
			{
				return new PhpMethodReflection($declaringClass, $reflection, $this->parser, $this->functionCallStatementFinder, true);
			}
			});
			$functionReflectionFactory = new class($this->getParser(), $functionCallStatementFinder) implements FunctionReflectionFactory {
				/** @var \PHPStan\Parser\Parser */
			private $parser;

				/** @var \PHPStan\Parser\FunctionCallStatementFinder */
			private $functionCallStatementFinder;

			public function __construct(Parser $parser, FunctionCallStatementFinder $functionCallStatementFinder)
			{
				$this->parser = $parser;
				$this->functionCallStatementFinder = $functionCallStatementFinder;
			}

			public function create(\ReflectionFunction $function): FunctionReflection
			{
				return new FunctionReflection($function, $this->parser, $this->functionCallStatementFinder, true);
			}
			};
			$this->broker = new Broker(array_merge([$phpExtension], $extensions), $functionReflectionFactory);
			$phpExtension->setBroker($this->broker);
		}

		return $this->broker;
	}

	private function assertError(string $message, string $file, int $line = null, Error $error)
	{
		$this->assertSame($file, $error->getFile(), $error->getMessage());
		$this->assertSame($line, $error->getLine(), $error->getMessage());
		$this->assertSame($message, $error->getMessage());
	}

	public function analyse(array $files, array $errors)
	{
		$result = $this->getAnalyser()->analyse($files);
		$this->assertInternalType('array', $result);
		foreach ($errors as $i => $error) {
			if (!isset($result[$i])) {
				$this->fail(
					sprintf(
						'Expected %d errors, but result contains only %d. Looking for error message: %s',
						count($errors),
						count($result),
						$error[0]
					)
				);
			}

			$this->assertError($error[0], $files[0], $error[1], $result[$i]);
		}

		$this->assertCount(
			count($errors),
			$result,
			sprintf(
				'Expected only %d errors, but result contains %d.',
				count($errors),
				count($result)
			)
		);
	}

}
