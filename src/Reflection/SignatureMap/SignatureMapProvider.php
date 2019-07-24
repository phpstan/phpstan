<?php declare(strict_types = 1);

namespace PHPStan\Reflection\SignatureMap;

class SignatureMapProvider
{

	/** @var \PHPStan\Reflection\SignatureMap\SignatureMapParser */
	private $parser;

	/** @var mixed[]|null */
	private static $signatureMap;

	/** @var array<string, array{hasSideEffects: bool}>|null */
	private static $functionMetadata;

	public function __construct(SignatureMapParser $parser)
	{
		$this->parser = $parser;
	}

	public function hasFunctionSignature(string $name): bool
	{
		$signatureMap = self::getSignatureMap();
		return array_key_exists(strtolower($name), $signatureMap);
	}

	public function getFunctionSignature(string $functionName, ?string $className): FunctionSignature
	{
		$functionName = strtolower($functionName);

		if (!$this->hasFunctionSignature($functionName)) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$signatureMap = self::getSignatureMap();

		return $this->parser->getFunctionSignature(
			$signatureMap[$functionName],
			$className
		);
	}

	public function hasFunctionMetadata(string $name): bool
	{
		$signatureMap = self::getFunctionMetadataMap();
		return array_key_exists(strtolower($name), $signatureMap);
	}

	public function getFunctionMetadata(string $functionName): FunctionSignature
	{
		$functionName = strtolower($functionName);

		if (!$this->hasFunctionMetadata($functionName)) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return self::getFunctionMetadataMap()[$functionName];
	}

	/**
	 * @return array<string, array{hasSideEffects: bool}>
	 */
	private static function getFunctionMetadataMap(): array
	{
		if (self::$functionMetadata === null) {
			/** @var array<string, array{hasSideEffects: bool}> $metadata */
			$metadata = require __DIR__ . '/functionMetadata.php';
			self::$functionMetadata = array_change_key_case($metadata, CASE_LOWER);
		}

		return self::$functionMetadata;
	}

	/**
	 * @return mixed[]
	 */
	private static function getSignatureMap(): array
	{
		if (self::$signatureMap === null) {
			$signatureMap = require __DIR__ . '/functionMap.php';
			if (!is_array($signatureMap)) {
				throw new \PHPStan\ShouldNotHappenException('Signature map could not be loaded.');
			}

			self::$signatureMap = array_change_key_case($signatureMap, CASE_LOWER);
		}

		return self::$signatureMap;
	}

}
