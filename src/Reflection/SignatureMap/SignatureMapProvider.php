<?php declare(strict_types = 1);

namespace PHPStan\Reflection\SignatureMap;

class SignatureMapProvider
{

	/** @var \PHPStan\Reflection\SignatureMap\SignatureMapParser */
	private $parser;

	/** @var mixed[]|null */
	private static $signatureMap;

	public function __construct(SignatureMapParser $parser)
	{
		$this->parser = $parser;
	}

	public function hasFunctionSignature(string $name): bool
	{
		$signatureMap = self::getSignatureMap();
		return array_key_exists($name, $signatureMap);
	}

	public function getFunctionSignature(string $name): FunctionSignature
	{
		if (!$this->hasFunctionSignature($name)) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$signatureMap = self::getSignatureMap();

		return $this->parser->getFunctionSignature($signatureMap[$name]);
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

			self::$signatureMap = $signatureMap;
		}

		return self::$signatureMap;
	}

}
