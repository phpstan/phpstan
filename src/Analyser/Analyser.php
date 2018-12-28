<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use Nette\Utils\Json;
use PHPStan\File\FileHelper;
use PHPStan\Parser\Parser;
use PHPStan\Rules\LineRuleError;
use PHPStan\Rules\Registry;

class Analyser
{

	/** @var \PHPStan\Analyser\ScopeFactory */
	private $scopeFactory;

	/** @var \PHPStan\Parser\Parser */
	private $parser;

	/** @var \PHPStan\Rules\Registry */
	private $registry;

	/** @var \PHPStan\Analyser\NodeScopeResolver */
	private $nodeScopeResolver;

	/** @var \PHPStan\File\FileHelper */
	private $fileHelper;

	/** @var (string|array<string, string>)[] */
	private $ignoreErrors;

	/** @var bool */
	private $reportUnmatchedIgnoredErrors;

	/** @var int */
	private $internalErrorsCountLimit;

	/**
	 * @param \PHPStan\Analyser\ScopeFactory $scopeFactory
	 * @param \PHPStan\Parser\Parser $parser
	 * @param \PHPStan\Rules\Registry $registry
	 * @param \PHPStan\Analyser\NodeScopeResolver $nodeScopeResolver
	 * @param \PHPStan\File\FileHelper $fileHelper
	 * @param (string|array<string, string>)[] $ignoreErrors
	 * @param bool $reportUnmatchedIgnoredErrors
	 * @param int $internalErrorsCountLimit
	 */
	public function __construct(
		ScopeFactory $scopeFactory,
		Parser $parser,
		Registry $registry,
		NodeScopeResolver $nodeScopeResolver,
		FileHelper $fileHelper,
		array $ignoreErrors,
		bool $reportUnmatchedIgnoredErrors,
		int $internalErrorsCountLimit
	)
	{
		$this->scopeFactory = $scopeFactory;
		$this->parser = $parser;
		$this->registry = $registry;
		$this->nodeScopeResolver = $nodeScopeResolver;
		$this->fileHelper = $fileHelper;
		$this->ignoreErrors = $ignoreErrors;
		$this->reportUnmatchedIgnoredErrors = $reportUnmatchedIgnoredErrors;
		$this->internalErrorsCountLimit = $internalErrorsCountLimit;
	}

	/**
	 * @param string[] $files
	 * @param bool $onlyFiles
	 * @param \Closure(string $file): void|null $preFileCallback
	 * @param \Closure(string $file): void|null $postFileCallback
	 * @param bool $debug
	 * @return string[]|\PHPStan\Analyser\Error[] errors
	 */
	public function analyse(
		array $files,
		bool $onlyFiles,
		?\Closure $preFileCallback = null,
		?\Closure $postFileCallback = null,
		bool $debug = false
	): array
	{
		$errors = [];

		foreach ($this->ignoreErrors as $ignoreError) {
			try {
				if (is_array($ignoreError)) {
					if (!isset($ignoreError['message'])) {
						$errors[] = sprintf(
							'Ignored error %s is missing a message.',
							Json::encode($ignoreError)
						);
						continue;
					}
					if (!isset($ignoreError['path'])) {
						$errors[] = sprintf(
							'Ignored error %s is missing a path.',
							Json::encode($ignoreError)
						);
					}

					$ignoreMessage = $ignoreError['message'];
				} else {
					$ignoreMessage = $ignoreError;
				}

				\Nette\Utils\Strings::match('', $ignoreMessage);
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

				if (is_file($file)) {
					$this->nodeScopeResolver->processNodes(
						$this->parser->parseFile($file),
						$this->scopeFactory->create(ScopeContext::create($file)),
						function (\PhpParser\Node $node, Scope $scope) use (&$fileErrors, $file): void {
							$uniquedAnalysedCodeExceptionMessages = [];
							foreach ($this->registry->getRules(get_class($node)) as $rule) {
								try {
									$ruleErrors = $rule->processNode($node, $scope);
								} catch (\PHPStan\AnalysedCodeException $e) {
									if (isset($uniquedAnalysedCodeExceptionMessages[$e->getMessage()])) {
										continue;
									}

									$uniquedAnalysedCodeExceptionMessages[$e->getMessage()] = true;
									$fileErrors[] = new Error($e->getMessage(), $file, $node->getLine(), false);
									continue;
								}

								foreach ($ruleErrors as $ruleError) {
									$line = $node->getLine();
									if (is_string($ruleError)) {
										$message = $ruleError;
									} else {
										$message = $ruleError->getMessage();
										if (
											$ruleError instanceof LineRuleError
											&& $ruleError->getLine() !== -1
										) {
											$line = $ruleError->getLine();
										}
									}
									$fileErrors[] = new Error($message, $scope->getFileDescription(), $line);
								}
							}
						}
					);
				} elseif (is_dir($file)) {
					$fileErrors[] = new Error(sprintf('File %s is a directory.', $file), $file, null, false);
				} else {
					$fileErrors[] = new Error(sprintf('File %s does not exist.', $file), $file, null, false);
				}
				if ($postFileCallback !== null) {
					$postFileCallback($file);
				}

				$errors = array_merge($errors, $fileErrors);
			} catch (\PhpParser\Error $e) {
				$errors[] = new Error($e->getMessage(), $file, $e->getStartLine() !== -1 ? $e->getStartLine() : null, false);
			} catch (\PHPStan\Parser\ParserErrorsException $e) {
				foreach ($e->getErrors() as $error) {
					$errors[] = new Error($error->getMessage(), $file, $error->getStartLine() !== -1 ? $error->getStartLine() : null, false);
				}
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
				if (IgnoredError::shouldIgnore($this->fileHelper, $error, $ignore)) {
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
					IgnoredError::stringifyPattern($unmatchedIgnoredError)
				);
			}
		}

		if ($reachedInternalErrorsCountLimit) {
			$errors[] = sprintf('Reached internal errors count limit of %d, exiting...', $this->internalErrorsCountLimit);
		}

		return $errors;
	}

}
