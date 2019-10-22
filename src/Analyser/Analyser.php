<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use Nette\Utils\Json;
use PHPStan\File\FileHelper;
use PHPStan\Node\FileNode;
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

	/** @var (string|mixed[])[] */
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
	 * @param \Closure(\PhpParser\Node $node, Scope $scope): void|null $outerNodeCallback
	 * @return string[]|\PHPStan\Analyser\Error[] errors
	 */
	public function analyse(
		array $files,
		bool $onlyFiles,
		?\Closure $preFileCallback = null,
		?\Closure $postFileCallback = null,
		bool $debug = false,
		?\Closure $outerNodeCallback = null
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
					if (!isset($ignoreError['path']) && !isset($ignoreError['paths'])) {
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
					$nodeCallback = function (\PhpParser\Node $node, Scope $scope) use (&$fileErrors, $file, &$scopeBenchmarkTime, $outerNodeCallback): void {
						$this->benchmarkEnd($scopeBenchmarkTime, 'scope');
						if ($outerNodeCallback !== null) {
							$outerNodeCallback($node, $scope);
						}
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
								$filePath = $scope->getFile();
								$traitFilePath = null;
								if ($scope->isInTrait()) {
									$traitReflection = $scope->getTraitReflection();
									if ($traitReflection->getFileName() !== false) {
										$traitFilePath = $traitReflection->getFileName();
									}
								}
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
										$filePath = $ruleError->getFile();
										$traitFilePath = null;
									}
								}
								$fileErrors[] = new Error(
									$message,
									$fileName,
									$line,
									true,
									$filePath,
									$traitFilePath
								);
							}
						}

						$scopeBenchmarkTime = $this->benchmarkStart();
					};

					$scopeBenchmarkTime = $this->benchmarkStart();
					$scope = $this->scopeFactory->create(ScopeContext::create($file));
					$nodeCallback(new FileNode($parserNodes), $scope);
					$this->nodeScopeResolver->processNodes(
						$parserNodes,
						$scope,
						$nodeCallback
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
				$shouldBeIgnored = false;
				if (is_string($ignore)) {
					$shouldBeIgnored = IgnoredError::shouldIgnore($this->fileHelper, $error, $ignore, null);
					if ($shouldBeIgnored) {
						unset($unmatchedIgnoredErrors[$i]);
					}
				} else {
					if (isset($ignore['path'])) {
						$shouldBeIgnored = IgnoredError::shouldIgnore($this->fileHelper, $error, $ignore['message'], $ignore['path']);
						if ($shouldBeIgnored) {
							if (isset($ignore['count'])) {
								$realCount = $unmatchedIgnoredErrors[$i]['realCount'] ?? 0;
								$realCount++;
								$unmatchedIgnoredErrors[$i]['realCount'] = $realCount;

								if ($realCount > $ignore['count']) {
									$shouldBeIgnored = false;
									if (!isset($unmatchedIgnoredErrors[$i]['file'])) {
										$unmatchedIgnoredErrors[$i]['file'] = $error->getFile();
										$unmatchedIgnoredErrors[$i]['line'] = $error->getLine();
									}
								}
							} else {
								unset($unmatchedIgnoredErrors[$i]);
							}
						}
					} elseif (isset($ignore['paths'])) {
						foreach ($ignore['paths'] as $j => $ignorePath) {
							$shouldBeIgnored = IgnoredError::shouldIgnore($this->fileHelper, $error, $ignore['message'], $ignorePath);
							if ($shouldBeIgnored) {
								if (isset($unmatchedIgnoredErrors[$i])) {
									if (!is_array($unmatchedIgnoredErrors[$i])) {
										throw new \PHPStan\ShouldNotHappenException();
									}
									unset($unmatchedIgnoredErrors[$i]['paths'][$j]);
									if (isset($unmatchedIgnoredErrors[$i]['paths']) && count($unmatchedIgnoredErrors[$i]['paths']) === 0) {
										unset($unmatchedIgnoredErrors[$i]);
									}
								}
								break;
							}
						}
					} else {
						throw new \PHPStan\ShouldNotHappenException();
					}
				}

				if ($shouldBeIgnored) {
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

		foreach ($unmatchedIgnoredErrors as $unmatchedIgnoredError) {
			if (!isset($unmatchedIgnoredError['count']) || !isset($unmatchedIgnoredError['realCount'])) {
				continue;
			}

			if ($unmatchedIgnoredError['realCount'] <= $unmatchedIgnoredError['count']) {
				continue;
			}

			$addErrors[] = new Error(sprintf(
				'Ignored error pattern %s is expected to occur %d %s, but occured %d %s.',
				IgnoredError::stringifyPattern($unmatchedIgnoredError),
				$unmatchedIgnoredError['count'],
				$unmatchedIgnoredError['count'] === 1 ? 'time' : 'times',
				$unmatchedIgnoredError['realCount'],
				$unmatchedIgnoredError['realCount'] === 1 ? 'time' : 'times'
			), $unmatchedIgnoredError['file'], $unmatchedIgnoredError['line']);
		}

		$errors = array_merge($errors, $addErrors);

		if (!$onlyFiles && $this->reportUnmatchedIgnoredErrors && !$reachedInternalErrorsCountLimit) {
			foreach ($unmatchedIgnoredErrors as $unmatchedIgnoredError) {
				if (
					isset($unmatchedIgnoredError['count'])
					&& isset($unmatchedIgnoredError['realCount'])
				) {
					if ($unmatchedIgnoredError['realCount'] < $unmatchedIgnoredError['count']) {
						$errors[] = sprintf(
							'Ignored error pattern %s is expected to occur %d %s, but occured only %d %s.',
							IgnoredError::stringifyPattern($unmatchedIgnoredError),
							$unmatchedIgnoredError['count'],
							$unmatchedIgnoredError['count'] === 1 ? 'time' : 'times',
							$unmatchedIgnoredError['realCount'],
							$unmatchedIgnoredError['realCount'] === 1 ? 'time' : 'times'
						);
					}
				} else {
					$errors[] = sprintf(
						'Ignored error pattern %s was not matched in reported errors.',
						IgnoredError::stringifyPattern($unmatchedIgnoredError)
					);
				}
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
