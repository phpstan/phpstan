<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\Parser\Parser;
use PHPStan\Rules\Registry;

class Analyser
{

	/**
	 * @var \PHPStan\Parser\Parser
	 */
	private $parser;

	/**
	 * @var \PHPStan\Rules\Registry
	 */
	private $registry;

	/**
	 * Directories to exclude from analysing
	 *
	 * @var string[]
	 */
	private $analyseExcludes;

	/**
	 * @var \PHPStan\Analyser\Scope
	 */
	private $scope;

	/**
	 * @var int
	 */
	private $level;

	/**
	 * @var int|null
	 */
	private $closureBindLevel;

	/**
	 * @param \PHPStan\Parser\Parser $parser
	 * @param \PHPStan\Rules\Registry $registry
	 * @param string[] $analyseExcludes
	 */
	public function __construct(
		Parser $parser,
		Registry $registry,
		array $analyseExcludes
	)
	{
		$this->parser = $parser;
		$this->registry = $registry;
		$this->analyseExcludes = $analyseExcludes;
	}

	/**
	 * @param string[] $files
	 * @param \Closure|null $progressCallback
	 * @return string[] errors
	 */
	public function analyse(array $files, \Closure $progressCallback = null)
	{
		$errors = [];

		foreach ($files as $file) {
			try {
				if ($this->isExcludedFromAnalysing($file)) {
					if ($progressCallback !== null) {
						$progressCallback($file);
					}
					continue;
				}

				$this->scope = new Scope(null, null, null, false);
				$this->level = 0;
				$fileErrors = $this->processNodes(
					$this->parser->parse($file),
					$file
				);
				if ($progressCallback !== null) {
					$progressCallback($file);
				}
				$errors = array_merge($errors, $fileErrors);
			} catch (\PhpParser\Error $e) {
				$errors[] = new Error($e->getMessage(), $file);
			} catch (\PHPStan\AnalysedCodeException $e) {
				$errors[] = new Error($e->getMessage(), $file);
			} catch (\Throwable $t) {
				\Tracy\Debugger::log($e);
				$errors[] = new Error(sprintf('Internal error: %s', $t->getMessage()), $file);
			}
		}

		return $errors;
	}

	/**
	 * @param \PhpParser\Node[]|\IteratorAggregate $nodes
	 * @param string $file
	 * @return string[] errors
	 */
	private function processNodes($nodes, string $file): array
	{
		$errors = [];
		foreach ($nodes as $node) {
			if (is_array($node)) {
				$this->level++;
				$errors = array_merge($errors, $this->processNodes($node, $file));
				continue;
			}
			if (!($node instanceof \PhpParser\Node)) {
				continue;
			}
			if ($node instanceof \PhpParser\Node\Stmt\Class_
				|| $node instanceof \PhpParser\Node\Stmt\Interface_
				|| $node instanceof \PhpParser\Node\Stmt\Trait_) {
				$this->scope = new Scope(
					isset($node->namespacedName) ? (string) $node->namespacedName : null,
					null,
					$this->scope->getNamespace(),
					false
				);
			}
			if ($node instanceof \PhpParser\Node\Stmt\Function_
				|| $node instanceof \PhpParser\Node\Stmt\ClassMethod) {
				$this->scope = new Scope(
					$this->scope->getClass(),
					($node instanceof \PhpParser\Node\Stmt\Function_ && (string) $node->namespacedName)
						? (string) $node->namespacedName
						: (string) $node->name,
					$this->scope->getNamespace(),
					false
				);
			}
			if ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
				$this->scope = new Scope(
					null,
					null,
					(string) $node->name,
					false
				);
			}

			$justStartedClosureBind = false;
			if (
				$node instanceof \PhpParser\Node\Expr\StaticCall
				&& (is_string($node->class) || $node->class instanceof \PhpParser\Node\Name)
				&& is_string($node->name)
			) {
				$className = (string) $node->class;
				$methodName = (string) $node->name;
				if ($className === 'Closure' && $methodName === 'bind') {
					$justStartedClosureBind = true;
					$this->closureBindLevel = $this->level;
					$this->scope = new Scope(
						$this->scope->getClass(),
						$this->scope->getFunction(),
						$this->scope->getNamespace(),
						true
					);
				}
			}

			if (
				$this->scope->isInClosureBind()
				&& !$justStartedClosureBind
				&& $this->level === $this->closureBindLevel
			) {
				$this->scope = new Scope(
					$this->scope->getClass(),
					$this->scope->getFunction(),
					$this->scope->getNamespace(),
					false
				);
			}

			foreach ($this->registry->getRules(get_class($node)) as $rule) {
				$ruleErrors = $this->createErrors(
					$node,
					$file,
					$rule->processNode(new Node($node, $this->scope))
				);
				$errors = array_merge($errors, $ruleErrors);
			}

			$this->level++;
			$errors = array_merge($errors, $this->processNodes($node, $file));
		}

		$this->level--;

		return $errors;
	}

	/**
	 * @param \PhpParser\Node $node
	 * @param string $file
	 * @param string[] $messages
	 * @return \PHPStan\Analyser\Error[]
	 */
	private function createErrors(\PhpParser\Node $node, string $file, array $messages): array
	{
		$errors = [];
		foreach ($messages as $message) {
			$errors[] = new Error($message, $file, $node->getLine());
		}
		return $errors;
	}

	/**
	 * @param string $file
	 * @return bool
	 */
	public function isExcludedFromAnalysing(string $file): bool
	{
		return $this->isExcluded($file, $this->analyseExcludes);
	}

	/**
	 * @param string $file
	 * @param string[] $excludes
	 * @return bool
	 */
	private function isExcluded(string $file, array $excludes): bool
	{
		foreach ($excludes as $exclude) {
			$realpathedExclude = realpath($exclude);
			if (($realpathedExclude !== false
				&& strpos($file, $realpathedExclude) === 0)
				|| fnmatch($exclude, $file)) {
				return true;
			}
		}

		return false;
	}

}
