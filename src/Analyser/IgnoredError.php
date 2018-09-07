<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class IgnoredError
{

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param array<string, string>|string $ignoredError
	 * @return string Representation of the ignored error
	 */
	public static function stringifyPattern($ignoredError): string
	{
		if (is_array($ignoredError)) {
			// ignore by path
			if (isset($ignoredError['path'])) {
				return sprintf('%s in path %s', $ignoredError['message'], $ignoredError['path']);
			}

			return $ignoredError['message'];
		}

		return $ignoredError;
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param Error $error
	 * @param array<string, string>|string $ignoredError
	 * @return bool To ignore or not to ignore?
	 */
	public static function shouldIgnore(Error $error, $ignoredError): bool
	{
		if (is_array($ignoredError)) {
			// ignore by path
			if (isset($ignoredError['path'])) {
				return \Nette\Utils\Strings::match($error->getMessage(), $ignoredError['message']) !== null
					&& \Nette\Utils\Strings::match(str_replace('\\', '/', $error->getFile()), $ignoredError['path']) !== null;
			}

			return false;
		}

		return \Nette\Utils\Strings::match($error->getMessage(), $ignoredError) !== null;
	}

}
