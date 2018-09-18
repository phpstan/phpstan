<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;

class MbRegexEncodingDynamicFunctionReturnTypeExtension extends MbStringAbstractBaseDynamicFunctionReturnTypeExtension
{

	/**
	 * List of supported encodings for mb_regex functions.
	 *
	 * @see https://secure.php.net/manual/en/mbstring.supported-encodings.php
	 *
	 * @return array<string, bool>
	 */
	private const MB_REGEX_ENCODINGS = [
		'ucs-4'       => true,
		'ucs-4LE'     => true,
		'utf-32'      => true,
		'utf-32BE'    => true,
		'utf-32LE'    => true,
		'utf-16'      => true,
		'utf-16BE'    => true,
		'utf-16LE'    => true,
		'utf-8'       => true,
		'ascii'       => true,
		'euc-jp'      => true,
		'sjis'        => true,
		'eucjp-win'   => true,
		'sjis-win'    => true,
		'iso-8859-1'  => true,
		'iso-8859-2'  => true,
		'iso-8859-3'  => true,
		'iso-8859-4'  => true,
		'iso-8859-5'  => true,
		'iso-8859-6'  => true,
		'iso-8859-7'  => true,
		'iso-8859-8'  => true,
		'iso-8859-9'  => true,
		'iso-8859-10' => true,
		'iso-8859-11' => true,
		'iso-8859-12' => true,
		'iso-8859-13' => true,
		'iso-8859-14' => true,
		'iso-8859-15' => true,
		'iso-8859-16' => true,
		'ecu-cn'      => true,
		'euc-tw'      => true,
		'big-5'       => true,
		'euc-kr'      => true,
		'koi8-r'      => true,
		'koi8-u'      => true,
		'auto'        => true,
	];

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'mb_regex_encoding' && $this->isMbstringExtensionLoaded();
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		// It always returns a string if no arguments are passed.
		if (!isset($functionCall->args[0])) {
			return new StringType();
		}

		$results = array_unique(array_map(function (ConstantStringType $encoding): bool {
			return $this->isSupportedEncoding($encoding->getValue());
		}, TypeUtils::getConstantStrings($scope->getType($functionCall->args[0]->value))));

		if (count($results) !== 1) {
			return new BooleanType();
		}

		return new ConstantBooleanType($results[0]);
	}

	protected function isSupportedEncoding(string $encoding): bool
	{
		return isset(self::MB_REGEX_ENCODINGS[strtolower($encoding)]);
	}

}
