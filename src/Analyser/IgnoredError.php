<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PHPStan\File\FileExcluder;
use PHPStan\File\FileHelper;

class IgnoredError
{

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param array<string, string>|string $ignoredError
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
		}

		return $ignoredError['message'];
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param FileHelper $fileHelper
	 * @param Error $error
	 * @param array<string, string>|string $ignoredError
	 * @return bool To ignore or not to ignore?
	 */
	public static function shouldIgnore(
		FileHelper $fileHelper,
		Error $error,
		$ignoredError
	): bool
	{
		if (is_array($ignoredError)) {
			// ignore by path
			if (isset($ignoredError['path'])) {
				$fileExcluder = new FileExcluder($fileHelper, [$ignoredError['path']]);

				return \Nette\Utils\Strings::match($error->getMessage(), $ignoredError['message']) !== null
					&& $fileExcluder->isExcludedFromAnalysing($error->getFile());
			}

			throw new \PHPStan\ShouldNotHappenException();
		}

		return \Nette\Utils\Strings::match($error->getMessage(), $ignoredError) !== null;
	}

}
