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
	 * @return array<string, int>
	 */
	private const MB_REGEX_ENCODINGS = [
		'ucs-4'       => 1,
		'ucs-4LE'     => 1,
		'utf-32'      => 1,
		'utf-32BE'    => 1,
		'utf-32LE'    => 1,
		'utf-16'      => 1,
		'utf-16BE'    => 1,
		'utf-16LE'    => 1,
		'utf-8'       => 1,
		'ascii'       => 1,
		'euc-jp'      => 1,
		'sjis'        => 1,
		'eucjp-win'   => 1,
		'sjis-win'    => 1,
		'iso-8859-1'  => 1,
		'iso-8859-2'  => 1,
		'iso-8859-3'  => 1,
		'iso-8859-4'  => 1,
		'iso-8859-5'  => 1,
		'iso-8859-6'  => 1,
		'iso-8859-7'  => 1,
		'iso-8859-8'  => 1,
		'iso-8859-9'  => 1,
		'iso-8859-10' => 1,
		'iso-8859-11' => 1,
		'iso-8859-12' => 1,
		'iso-8859-13' => 1,
		'iso-8859-14' => 1,
		'iso-8859-15' => 1,
		'iso-8859-16' => 1,
		'ecu-cn'      => 1,
		'euc-tw'      => 1,
		'big-5'       => 1,
		'euc-kr'      => 1,
		'koi8-r'      => 1,
		'koi8-u'      => 1,
		'auto'        => 1,
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
