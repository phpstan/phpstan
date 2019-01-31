<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use Nette\Utils\Json;
use PHPStan\File\FileHelper;
use PHPStan\Parser\Parser;
use PHPStan\Rules\FileRuleError;
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

	/** @var string|null */
	private $benchmarkFile;

	/** @var float[] */
	private $benchmarkData = [];

	/**
	 * @param \PHPStan\Analyser\ScopeFactory $scopeFactory
	 * @param \PHPStan\Parser\Parser $parser
	 * @param \PHPStan\Rules\Registry $registry
	 * @param \PHPStan\Analyser\NodeScopeResolver $nodeScopeResolver
	 * @param \PHPStan\File\FileHelper $fileHelper
	 * @param (string|array<string, string>)[] $ignoreErrors
	 * @param bool $reportUnmatchedIgnoredErrors
	 * @param int $internalErrorsCountLimit
	 * @param string|null $benchmarkFile
	 */
	public function __construct(
		ScopeFactory $scopeFactory,
		Parser $parser,
		Registry $registry,
		NodeScopeResolver $nodeScopeResolver,
		FileHelper $fileHelper,
		array $ignoreErrors,
		bool $reportUnmatchedIgnoredErrors,
		int $internalErrorsCountLimit,
		?string $benchmarkFile = null
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
		$this->benchmarkFile = $benchmarkFile;
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
					$parserBenchmarkTime = $this->benchmarkStart();
					$parserNodes = $this->parser->parseFile($file);
					$this->benchmarkEnd($parserBenchmarkTime, 'parser');

					$scopeBenchmarkTime = $this->benchmarkStart();
					$this->nodeScopeResolver->processNodes(
						$parserNodes,
						$this->scopeFactory->create(ScopeContext::create($file)),
						function (\PhpParser\Node $node, Scope $scope) use (&$fileErrors, $file, &$scopeBenchmarkTime): void {
							$this->benchmarkEnd($scopeBenchmarkTime, 'scope');
							$uniquedAnalysedCodeExceptionMessages = [];
							foreach ($this->registry->getRules(get_class($node)) as $rule) {
								try {
									$ruleBenchmarkTime = $this->benchmarkStart();
									$ruleErrors = $rule->processNode($node, $scope);
									$this->benchmarkEnd($ruleBenchmarkTime, sprintf('rule-%s', get_class($rule)));
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
									$fileName = $scope->getFileDescription();
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
										if (
											$ruleError instanceof FileRuleError
											&& $ruleError->getFile() !== ''
										) {
											$fileName = $ruleError->getFile();
										}
									}
									$fileErrors[] = new Error($message, $fileName, $line);
								}
							}

							$scopeBenchmarkTime = $this->benchmarkStart();
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

		if ($this->benchmarkFile !== null) {
			uasort($this->benchmarkData, static function (float $a, float $b): int {
				return $b <=> $a;
			});
			file_put_contents($this->benchmarkFile, Json::encode($this->benchmarkData, Json::PRETTY));
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

	private function benchmarkStart(): ?float
	{
		if ($this->benchmarkFile === null) {
			return null;
		}

		return microtime(true);
	}

	private function benchmarkEnd(?float $startTime, string $description): void
	{
		if ($this->benchmarkFile === null) {
			return;
		}
		if ($startTime === null) {
			return;
		}
		$elapsedTime = microtime(true) - $startTime;
		if (!isset($this->benchmarkData[$description])) {
			$this->benchmarkData[$description] = $elapsedTime;
			return;
		}

		$this->benchmarkData[$description] += $elapsedTime;
	}

}
