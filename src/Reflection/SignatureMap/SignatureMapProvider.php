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

	/**
	 * @param string $functionName
	 * @return array{hasSideEffects: bool}
	 */
	public function getFunctionMetadata(string $functionName): array
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

			$signatureMap = array_change_key_case($signatureMap, CASE_LOWER);

			if (PHP_VERSION_ID >= 70400) {
				$php74MapDelta = require __DIR__ . '/functionMap_php74delta.php';
				if (!is_array($php74MapDelta)) {
					throw new \PHPStan\ShouldNotHappenException('Signature map could not be loaded.');
				}

				$signatureMap = self::computeSignatureMap($signatureMap, $php74MapDelta);
			}

			self::$signatureMap = $signatureMap;
		}

		return self::$signatureMap;
	}

	/**
	 * @param array<string, mixed> $signatureMap
	 * @param array<string, array<string, mixed>> $delta
	 * @return array<string, mixed>
	 */
	private static function computeSignatureMap(array $signatureMap, array $delta): array
	{
		foreach ($delta['old'] as $key) {
			unset($signatureMap[strtolower($key)]);
		}
		foreach ($delta['new'] as $key => $signature) {
			$signatureMap[strtolower($key)] = $signature;
		}

		return $signatureMap;
	}

}
