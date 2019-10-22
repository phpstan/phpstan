<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\File\FileExcluder;
use PHPStan\File\FileHelper;

class IgnoredError
{

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param mixed[]|string $ignoredError
	 * @return string Representation of the ignored error
	 */
	public static function stringifyPattern($ignoredError): string
	{
		if (!is_array($ignoredError)) {
			return $ignoredError;
		}

		// ignore by path
		if (isset($ignoredError['path'])) {
			return sprintf('%s in path %s', $ignoredError['message'], $ignoredError['path']);
		} elseif (isset($ignoredError['paths'])) {
			if (count($ignoredError['paths']) === 1) {
				return sprintf('%s in path %s', $ignoredError['message'], implode(', ', $ignoredError['paths']));

			}
			return sprintf('%s in paths: %s', $ignoredError['message'], implode(', ', $ignoredError['paths']));
		}

		return $ignoredError['message'];
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param FileHelper $fileHelper
	 * @param Error $error
	 * @param string $ignoredErrorPattern
	 * @param string|null $path
	 * @return bool To ignore or not to ignore?
	 */
	public static function shouldIgnore(
		FileHelper $fileHelper,
		Error $error,
		string $ignoredErrorPattern,
		?string $path
	): bool
	{
		if ($path !== null) {
			$fileExcluder = new FileExcluder($fileHelper, [$path]);

			if (\Nette\Utils\Strings::match($error->getMessage(), $ignoredErrorPattern) === null) {
				return false;
			}

			$isExcluded = $fileExcluder->isExcludedFromAnalysing($error->getFilePath());
			if (!$isExcluded && $error->getTraitFilePath() !== null) {
				return $fileExcluder->isExcludedFromAnalysing($error->getTraitFilePath());
			}

			return $isExcluded;
		}

		return \Nette\Utils\Strings::match($error->getMessage(), $ignoredErrorPattern) !== null;
	}

}
