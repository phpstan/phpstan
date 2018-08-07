<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PHPStan\Type\DynamicFunctionReturnTypeExtension;

abstract class MbStringAbstractBaseDynamicFunctionReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{

	/**
	 * Wether the MBString extension is loaded.
	 *
	 * @var bool|null
	 */
	private static $extensionLoaded;

	/**
	 * The list of supported encodings and their aliases.
	 *
	 * @var array<string, int>|null
	 */
	private static $supportedEncodings;

	/**
	 * Returns whether or not the MBString extension is loaded.
	 *
	 * @return bool
	 */
	protected function isMbstringExtensionLoaded(): bool
	{
		if (self::$extensionLoaded === null) {
			self::$extensionLoaded = extension_loaded('mbstring');
		}

		return self::$extensionLoaded;
	}

	/**
	 * Returns whether or not the given encoding is supported.
	 *
	 * @param string $encoding
	 *
	 * @return bool
	 */
	protected function isSupportedEncoding(string $encoding): bool
	{
		return isset($this->getSupportedEncodings()[strtolower($encoding)]);
	}

	/**
	 * Gets the list of supported encodings and their aliases for the version of PHP that PHPStan is being run on.
	 *
	 * @return array<string, int>
	 */
	private function getSupportedEncodings(): array
	{
		if (self::$supportedEncodings !== null) {
			return self::$supportedEncodings;
		}

		if (!$this->isMbstringExtensionLoaded()) {
			self::$supportedEncodings = [];

			return self::$supportedEncodings;
		}

		// Foreach encoding in the list of supported encodings, merge the lists of encodings and then flip the
		// array. This makes it so that the encoding names become the array keys and filters out duplicates at the
		// same time. The values of the array are irrelevant.
		self::$supportedEncodings = array_flip(array_merge(...array_map(function (string $encoding): array {
			$aliases = mb_encoding_aliases($encoding);

			// This should never occur here since $encoding can't be anything but a known encoding.
			if ($aliases === false) {
				return [];
			}

			// Foreach encoding alias of $encoding, convert it to lowercase; then append $encoding, converted to
			// lowercase, to the list of encodings and return that list.
			$encodings = array_map('strtolower', $aliases);
			$encodings[] = strtolower($encoding);

			return $encodings;
		}, mb_list_encodings())));

		return self::$supportedEncodings;
	}

}
