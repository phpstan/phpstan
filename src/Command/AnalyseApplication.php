<?php declare(strict_types = 1);

namespace PHPStan\Command;

use PhpParser\Node;
use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\Scope;
use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use PHPStan\File\FileHelper;
use PHPStan\Type\MixedType;
use Symfony\Component\Console\Style\OutputStyle;

class AnalyseApplication
{

	/** @var \PHPStan\Analyser\Analyser */
	private $analyser;

	/** @var string */
	private $memoryLimitFile;

	/** @var \PHPStan\File\FileHelper */
	private $fileHelper;

	/** @var string */
	private $currentWorkingDirectory;

	public function __construct(
		Analyser $analyser,
		string $memoryLimitFile,
		FileHelper $fileHelper,
		string $currentWorkingDirectory
	)
	{
		$this->analyser = $analyser;
		$this->memoryLimitFile = $memoryLimitFile;
		$this->fileHelper = $fileHelper;
		$this->currentWorkingDirectory = $currentWorkingDirectory;
	}

	/**
	 * @param string[] $files
	 * @param bool $onlyFiles
	 * @param \Symfony\Component\Console\Style\OutputStyle $style
	 * @param \PHPStan\Command\ErrorFormatter\ErrorFormatter $errorFormatter
	 * @param bool $defaultLevelUsed
	 * @param bool $debug
	 * @param string|null $projectConfigFile
	 * @return int Error code.
	 */
	public function analyse(
		array $files,
		bool $onlyFiles,
		OutputStyle $style,
		ErrorFormatter $errorFormatter,
		bool $defaultLevelUsed,
		bool $debug,
		?string $projectConfigFile
	): int
	{
		$this->updateMemoryLimitFile();
		$errors = [];

		if (!$debug) {
			$progressStarted = false;
			$fileOrder = 0;
			$preFileCallback = null;
			$postFileCallback = function () use ($style, &$progressStarted, $files, &$fileOrder): void {
				if (!$progressStarted) {
					$style->progressStart(count($files));
					$progressStarted = true;
				}
				$style->progressAdvance();
				if ($fileOrder % 100 === 0) {
					$this->updateMemoryLimitFile();
				}
				$fileOrder++;
			};
		} else {
			$preFileCallback = static function (string $file) use ($style): void {
				$style->writeln($file);
			};
			$postFileCallback = null;
		}

		$hasInferrablePropertyTypesFromConstructor = false;
		$errors = array_merge($errors, $this->analyser->analyse(
			$files,
			$onlyFiles,
			$preFileCallback,
			$postFileCallback,
			$debug,
			static function (Node $node, Scope $scope) use (&$hasInferrablePropertyTypesFromConstructor): void {
				if ($hasInferrablePropertyTypesFromConstructor) {
					return;
				}

				if (!$node instanceof Node\Stmt\PropertyProperty) {
					return;
				}

				if (!$scope->isInClass()) {
					return;
				}

				$classReflection = $scope->getClassReflection();
				if (!$classReflection->hasConstructor() || $classReflection->getConstructor()->getDeclaringClass()->getName() !== $classReflection->getName()) {
					return;
				}
				$propertyName = $node->name->toString();
				if (!$classReflection->hasNativeProperty($propertyName)) {
					return;
				}
				$propertyReflection = $classReflection->getNativeProperty($propertyName);
				if (!$propertyReflection->isPrivate()) {
					return;
				}
				$propertyType = $propertyReflection->getType();
				if (!$propertyType instanceof MixedType || $propertyType->isExplicitMixed()) {
					return;
				}

				$hasInferrablePropertyTypesFromConstructor = true;
			}
		));

		if (isset($progressStarted) && $progressStarted) {
			$style->progressFinish();
		}

		$fileSpecificErrors = [];
		$notFileSpecificErrors = [];
		foreach ($errors as $error) {
			if (is_string($error)) {
				$notFileSpecificErrors[] = $error;
			} else {
				$fileSpecificErrors[] = $error;
			}
		}

		return $errorFormatter->formatErrors(
			new AnalysisResult(
				$fileSpecificErrors,
				$notFileSpecificErrors,
				$defaultLevelUsed,
				$this->fileHelper->normalizePath($this->currentWorkingDirectory),
				$hasInferrablePropertyTypesFromConstructor,
				$projectConfigFile
			),
			$style
		);
	}

	private function updateMemoryLimitFile(): void
	{
		$bytes = memory_get_peak_usage(true);
		$megabytes = ceil($bytes / 1024 / 1024);
		file_put_contents($this->memoryLimitFile, sprintf('%d MB', $megabytes));
	}

}
