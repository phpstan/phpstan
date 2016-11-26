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
	 * @var \PhpParser\PrettyPrinter\Standard
	 */
	private $printer;

	/**
	 * Directories to exclude from analysing
	 *
	 * @var string[]
	 */
	private $analyseExcludes;

	/**
	 * @var string[]
	 */
	private $ignoreErrors;

	/**
	 * @var string|null
	 */
	private $bootstrapFile;

	/**
	 * @param \PHPStan\Broker\Broker $broker
	 * @param \PHPStan\Parser\Parser $parser
	 * @param \PHPStan\Rules\Registry $registry
	 * @param \PHPStan\Analyser\NodeScopeResolver $nodeScopeResolver
	 * @param \PhpParser\PrettyPrinter\Standard $printer
	 * @param string[] $analyseExcludes
	 * @param string[] $ignoreErrors
	 * @param string|null $bootstrapFile
	 */
	public function __construct(
		Broker $broker,
		Parser $parser,
		Registry $registry,
		NodeScopeResolver $nodeScopeResolver,
		\PhpParser\PrettyPrinter\Standard $printer,
		array $analyseExcludes,
		array $ignoreErrors,
		string $bootstrapFile = null
	)
	{
		$this->broker = $broker;
		$this->parser = $parser;
		$this->registry = $registry;
		$this->nodeScopeResolver = $nodeScopeResolver;
		$this->printer = $printer;
		$this->analyseExcludes = array_map(function (string $exclude): string {
			return str_replace('/', DIRECTORY_SEPARATOR, $exclude);
		}, $analyseExcludes);
		$this->ignoreErrors = $ignoreErrors;
		$this->bootstrapFile = $bootstrapFile;
	}

	/**
	 * @param string[] $files
	 * @param \Closure|null $progressCallback
	 * @return string[]|\PHPStan\Analyser\Error[] errors
	 */
	public function analyse(array $files, \Closure $progressCallback = null): array
	{
		$errors = [];

		if ($this->bootstrapFile !== null) {
			if (!is_file($this->bootstrapFile)) {
				return [
					sprintf('Bootstrap file %s does not exist.', $this->bootstrapFile),
				];
			}
			try {
				require_once $this->bootstrapFile;
			} catch (\Throwable $e) {
				return [$e->getMessage()];
			}
		}

		foreach ($this->ignoreErrors as $ignoreError) {
			try {
				\Nette\Utils\Strings::match('', $ignoreError);
			} catch (\Nette\Utils\RegexpException $e) {
				$errors[] = $e->getMessage();
			}
		}

		if (count($errors) > 0) {
			return $errors;
		}

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
					new Scope($this->broker, $this->printer, $file),
					function (\PhpParser\Node $node, Scope $scope) use (&$fileErrors) {
						$classes = array_merge([get_class($node)], class_parents($node));
						foreach ($this->registry->getRules($classes) as $rule) {
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

		$unmatchedIgnoredErrors = $this->ignoreErrors;
		$errors = array_values(array_filter($errors, function (string $error) use (&$unmatchedIgnoredErrors): bool {
			foreach ($this->ignoreErrors as $i => $ignore) {
				if (\Nette\Utils\Strings::match($error, $ignore) !== null) {
					unset($unmatchedIgnoredErrors[$i]);
					return false;
				}
			}

			return true;
		}));

		foreach ($unmatchedIgnoredErrors as $unmatchedIgnoredError) {
			$errors[] = sprintf(
				'Ignored error pattern %s was not matched in reported errors.',
				$unmatchedIgnoredError
			);
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

	public function isExcludedFromAnalysing(string $file): bool
	{
		foreach ($this->analyseExcludes as $exclude) {
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
