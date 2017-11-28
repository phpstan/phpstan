<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\Broker\Broker;
use PHPStan\File\FileHelper;
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
	 * @var \PHPStan\Analyser\TypeSpecifier
	 */
	private $typeSpecifier;

	/**
	 * @var string[]
	 */
	private $ignoreErrors;

	/**
	 * @var string|null
	 */
	private $bootstrapFile;

	/**
	 * @var bool
	 */
	private $reportUnmatchedIgnoredErrors;

	/**
	 * @var int
	 */
	private $internalErrorsCountLimit;

	/**
	 * @param \PHPStan\Broker\Broker $broker
	 * @param \PHPStan\Parser\Parser $parser
	 * @param \PHPStan\Rules\Registry $registry
	 * @param \PHPStan\Analyser\NodeScopeResolver $nodeScopeResolver
	 * @param \PhpParser\PrettyPrinter\Standard $printer
	 * @param \PHPStan\Analyser\TypeSpecifier $typeSpecifier
	 * @param \PHPStan\File\FileHelper $fileHelper
	 * @param string[] $ignoreErrors
	 * @param string|null $bootstrapFile
	 * @param bool $reportUnmatchedIgnoredErrors
	 * @param int $internalErrorsCountLimit
	 */
	public function __construct(
		Broker $broker,
		Parser $parser,
		Registry $registry,
		NodeScopeResolver $nodeScopeResolver,
		\PhpParser\PrettyPrinter\Standard $printer,
		TypeSpecifier $typeSpecifier,
		FileHelper $fileHelper,
		array $ignoreErrors,
		string $bootstrapFile = null,
		bool $reportUnmatchedIgnoredErrors,
		int $internalErrorsCountLimit
	)
	{
		$this->broker = $broker;
		$this->parser = $parser;
		$this->registry = $registry;
		$this->nodeScopeResolver = $nodeScopeResolver;
		$this->printer = $printer;
		$this->typeSpecifier = $typeSpecifier;
		$this->ignoreErrors = $ignoreErrors;
		$this->bootstrapFile = $bootstrapFile !== null ? $fileHelper->normalizePath($bootstrapFile) : null;
		$this->reportUnmatchedIgnoredErrors = $reportUnmatchedIgnoredErrors;
		$this->internalErrorsCountLimit = $internalErrorsCountLimit;
	}

	/**
	 * @param string[] $files
	 * @param bool $onlyFiles
	 * @param \Closure|null $preFileCallback
	 * @param \Closure|null $postFileCallback
	 * @param bool $debug
	 * @return string[]|\PHPStan\Analyser\Error[] errors
	 */
	public function analyse(
		array $files,
		bool $onlyFiles,
		\Closure $preFileCallback = null,
		\Closure $postFileCallback = null,
		bool $debug = false
	): array
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

		$this->nodeScopeResolver->setAnalysedFiles($files);
		$internalErrorsCount = 0;
		$reachedInternalErrorsCountLimit = false;
		foreach ($files as $file) {
			try {
				$fileErrors = [];
				if ($preFileCallback !== null) {
					$preFileCallback($file);
				}
				$this->nodeScopeResolver->processNodes(
					$this->parser->parseFile($file),
					new Scope($this->broker, $this->printer, $this->typeSpecifier, $file),
					function (\PhpParser\Node $node, Scope $scope) use (&$fileErrors) {
						foreach ($this->registry->getRules(get_class($node)) as $rule) {
							$ruleErrors = $this->createErrors(
								$node,
								$scope->getAnalysedContextFile(),
								$rule->processNode($node, $scope)
							);
							$fileErrors = array_merge($fileErrors, $ruleErrors);
						}
					}
				);
				if ($postFileCallback !== null) {
					$postFileCallback($file);
				}

				$errors = array_merge($errors, $fileErrors);
			} catch (\PhpParser\Error $e) {
				$errors[] = new Error($e->getMessage(), $file, $e->getStartLine() !== -1 ? $e->getStartLine() : null, false);
			} catch (\PHPStan\AnalysedCodeException $e) {
				$errors[] = new Error($e->getMessage(), $file, null, false);
			} catch (\Throwable $t) {
				if ($debug) {
					throw $t;
				}
				$internalErrorsCount++;
				$internalErrorMessage = sprintf('Internal error: %s', $t->getMessage());
				$internalErrorMessage .= sprintf(
					'%sRun PHPStan with --debug option and post the stack trace to:%s%s',
					"\n",
					"\n",
					'https://github.com/phpstan/phpstan/issues/new'
				);
				$errors[] = new Error($internalErrorMessage, $file);
				if ($internalErrorsCount >= $this->internalErrorsCountLimit) {
					$reachedInternalErrorsCountLimit = true;
					break;
				}
			}
		}

		$unmatchedIgnoredErrors = $this->ignoreErrors;
		$addErrors = [];
		$errors = array_values(array_filter($errors, function (Error $error) use (&$unmatchedIgnoredErrors, &$addErrors): bool {
			foreach ($this->ignoreErrors as $i => $ignore) {
				if (\Nette\Utils\Strings::match($error->getMessage(), $ignore) !== null) {
					unset($unmatchedIgnoredErrors[$i]);
					if (!$error->canBeIgnored()) {
						$addErrors[] = sprintf(
							'Error message "%s" cannot be ignored, use excludes_analyse instead.',
							$error->getMessage()
						);
						return true;
					}
					return false;
				}
			}

			return true;
		}));

		$errors = array_merge($errors, $addErrors);

		if (!$onlyFiles && $this->reportUnmatchedIgnoredErrors && !$reachedInternalErrorsCountLimit) {
			foreach ($unmatchedIgnoredErrors as $unmatchedIgnoredError) {
				$errors[] = sprintf(
					'Ignored error pattern %s was not matched in reported errors.',
					$unmatchedIgnoredError
				);
			}
		}

		if ($reachedInternalErrorsCountLimit) {
			$errors[] = sprintf('Reached internal errors count limit of %d, exiting...', $this->internalErrorsCountLimit);
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

}
