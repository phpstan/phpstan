<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\Broker\Broker;
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
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @var \PHPStan\Analyser\NodeScopeResolver
	 */
	private $nodeScopeResolver;

	/**
	 * Directories to exclude from analysing
	 *
	 * @var string[]
	 */
	private $analyseExcludes;

	/**
	 * @param \PHPStan\Broker\Broker $broker
	 * @param \PHPStan\Parser\Parser $parser
	 * @param \PHPStan\Rules\Registry $registry
	 * @param \PHPStan\Analyser\NodeScopeResolver $nodeScopeResolver
	 * @param string[] $analyseExcludes
	 */
	public function __construct(
		Broker $broker,
		Parser $parser,
		Registry $registry,
		NodeScopeResolver $nodeScopeResolver,
		array $analyseExcludes
	)
	{
		$this->broker = $broker;
		$this->parser = $parser;
		$this->registry = $registry;
		$this->nodeScopeResolver = $nodeScopeResolver;
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

				$fileErrors = [];
				$this->nodeScopeResolver->processNodes(
					$this->parser->parseFile($file),
					new Scope($this->broker, $file),
					function (\PhpParser\Node $node, Scope $scope) use (&$fileErrors) {
						foreach ($this->registry->getRules(get_class($node)) as $rule) {
							$ruleErrors = $this->createErrors(
								$node,
								$scope->getFile(),
								$rule->processNode($node, $scope)
							);
							$fileErrors = array_merge($fileErrors, $ruleErrors);
						}
					}
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
				\Tracy\Debugger::log($t);
				$errors[] = new Error(sprintf('Internal error: %s', $t->getMessage()), $file);
			}
		}

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
