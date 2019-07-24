<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Command\AnalyseCommand;
use PHPStan\Command\AnalysisResult;
use PHPStan\File\RelativePathHelper;
use Symfony\Component\Console\Style\OutputStyle;

class TableErrorFormatter implements ErrorFormatter
{

	/** @var RelativePathHelper */
	private $relativePathHelper;

	/** @var bool */
	private $showTipsOfTheDay;

	/** @var bool */
	private $checkThisOnly;

	/** @var bool */
	private $inferPrivatePropertyTypeFromConstructor;

	public function __construct(
		RelativePathHelper $relativePathHelper,
		bool $showTipsOfTheDay,
		bool $checkThisOnly,
		bool $inferPrivatePropertyTypeFromConstructor
	)
	{
		$this->relativePathHelper = $relativePathHelper;
		$this->showTipsOfTheDay = $showTipsOfTheDay;
		$this->checkThisOnly = $checkThisOnly;
		$this->inferPrivatePropertyTypeFromConstructor = $inferPrivatePropertyTypeFromConstructor;
	}

	public function formatErrors(
		AnalysisResult $analysisResult,
		OutputStyle $style
	): int
	{
		if (!$analysisResult->hasErrors()) {
			$style->success('No errors');
			if ($this->showTipsOfTheDay) {
				if ($analysisResult->isDefaultLevelUsed()) {
					$style->writeln('💡 Tip of the Day:');
					$style->writeln(sprintf(
						"PHPStan is performing only the most basic checks.\nYou can pass a higher rule level through the <fg=cyan>--%s</> option\n(the default and current level is %d) to analyse code more thoroughly.",
						AnalyseCommand::OPTION_LEVEL,
						AnalyseCommand::DEFAULT_LEVEL
					));
					$style->writeln('');
				} elseif (
					!$this->checkThisOnly
					&& $analysisResult->hasInferrablePropertyTypesFromConstructor()
					&& !$this->inferPrivatePropertyTypeFromConstructor
				) {
					$projectConfigFile = 'phpstan.neon';
					if ($analysisResult->getProjectConfigFile() !== null) {
						$projectConfigFile = $this->relativePathHelper->getRelativePath($analysisResult->getProjectConfigFile());
					}
					$style->writeln('💡 Tip of the Day:');
					$style->writeln("One or more properties in your code do not have a phpDoc with a type\nbut it could be inferred from the constructor to find more bugs.");
					$style->writeln(sprintf('Use <fg=cyan>inferPrivatePropertyTypeFromConstructor: true</> in your <fg=cyan>%s</> to try it out!', $projectConfigFile));
					$style->writeln('');
				}
			}

			return 0;
		}

		/** @var array<string, \PHPStan\Analyser\Error[]> $fileErrors */
		$fileErrors = [];
		foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
			if (!isset($fileErrors[$fileSpecificError->getFile()])) {
				$fileErrors[$fileSpecificError->getFile()] = [];
			}

			$fileErrors[$fileSpecificError->getFile()][] = $fileSpecificError;
		}

		foreach ($fileErrors as $file => $errors) {
			$rows = [];
			foreach ($errors as $error) {
				$rows[] = [
					(string) $error->getLine(),
					$error->getMessage(),
				];
			}

			$relativeFilePath = $this->relativePathHelper->getRelativePath($file);

			$style->table(['Line', $relativeFilePath], $rows);
		}

		if (count($analysisResult->getNotFileSpecificErrors()) > 0) {
			$style->table(['Error'], array_map(static function (string $error): array {
				return [$error];
			}, $analysisResult->getNotFileSpecificErrors()));
		}

		$style->error(sprintf($analysisResult->getTotalErrorsCount() === 1 ? 'Found %d error' : 'Found %d errors', $analysisResult->getTotalErrorsCount()));
		return 1;
	}

}
