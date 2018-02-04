<?php declare(strict_types = 1);

namespace PHPStan\Reflection\SignatureMap;

use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

class SignatureMapParser
{

	/**
	 * @var \PHPStan\PhpDoc\TypeNodeResolver
	 */
	private $typeNodeResolver;

	public function __construct(
		TypeNodeResolver $typeNodeResolver
	)
	{
		$this->typeNodeResolver = $typeNodeResolver;
	}

	/**
	 * @param mixed[] $map
	 * @return \PHPStan\Reflection\SignatureMap\FunctionSignature[]
	 */
	public function getFunctions(array $map): array
	{
		$signatures = [];

		foreach ($map as $functionName => $functionMap) {
			if (strpos($functionName, '::') !== false) {
				// do not extract methods yet
				continue;
			}
			if (\Nette\Utils\Strings::match($functionName, "#\\'\d+$#") !== null) {
				// skip alternative signatures
				continue;
			}
			$parameterSignatures = $this->getParameters(array_slice($functionMap, 1));
			$hasVariadic = false;
			foreach ($parameterSignatures as $parameterSignature) {
				if ($parameterSignature->isVariadic()) {
					$hasVariadic = true;
					break;
				}
			}
			$signatures[$functionName] = new FunctionSignature(
				$parameterSignatures,
				$this->getTypeFromString($functionMap[0]),
				$hasVariadic
			);
		}

		return $signatures;
	}

	private function getTypeFromString(string $typeString): Type
	{
		if ($typeString === '') {
			return new MixedType();
		}
		$parts = explode('|', $typeString);
		$types = [];
		foreach ($parts as $part) {
			$isNullable = false;
			if (substr($part, 0, 1) === '?') {
				$isNullable = true;
				$part = substr($part, 1);
			}

			$type = $this->typeNodeResolver->resolve(new IdentifierTypeNode($part), new NameScope());
			if ($isNullable) {
				$type = TypeCombinator::addNull($type);
			}

			$types[] = $type;
		}

		return TypeCombinator::union(...$types);
	}

	/**
	 * @param string[] $parameterMap
	 * @return \PHPStan\Reflection\SignatureMap\ParameterSignature[]
	 */
	private function getParameters(array $parameterMap): array
	{
		$parameterSignatures = [];
		foreach ($parameterMap as $parameterName => $typeString) {
			[$name, $isOptional, $isPassedByReference, $isVariadic] = $this->getParameterInfoFromName($parameterName);
			$parameterSignatures[] = new ParameterSignature(
				$name,
				$isOptional,
				$this->getTypeFromString($typeString),
				$isPassedByReference,
				$isVariadic
			);
		}

		return $parameterSignatures;
	}

	/**
	 * @param string $parameterNameString
	 * @return mixed[]
	 */
	private function getParameterInfoFromName(string $parameterNameString): array
	{
		$matches = \Nette\Utils\Strings::match(
			$parameterNameString,
			'#^(?P<reference>&r?w?_?)?(?P<variadic>\.\.\.)?(?P<name>[^=]+)?(?P<optional>=)?($)#'
		);
		if ($matches === null || !isset($matches['optional'])) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		$isPassedByReference = $matches['reference'] !== '';
		$isVariadic = $matches['variadic'] !== '';
		$isOptional = $matches['optional'] !== '';

		$name = $matches['name'] !== '' ? $matches['name'] : '...';

		return [$name, $isOptional, $isPassedByReference, $isVariadic];
	}

}
