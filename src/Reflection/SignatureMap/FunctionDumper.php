<?php declare(strict_types = 1);

namespace PHPStan\Reflection\SignatureMap;

class FunctionDumper
{

	public function getFilename(string $functionName): string
	{
		$functionName = strtolower(str_replace('\\', '_', $functionName));

		return sprintf(
			'%s/functions/%s/%s.php',
			__DIR__,
			substr($functionName, 0, 2),
			$functionName
		);
	}

	public function dump(string $functionName, FunctionSignature $functionSignature): void
	{
		$filename = $this->getFilename($functionName);
		@mkdir(dirname($filename), 0755);

		file_put_contents($filename, $this->getContents($functionName, $functionSignature));
		chmod($filename, 0755);
	}

	private function getContents(string $functionName, FunctionSignature $functionSignature): string
	{
		$parametersDump = '';
		foreach ($functionSignature->getParameters() as $parameterSignature) {
			$parametersDump .= sprintf(
				'       new \PHPStan\Reflection\Native\NativeParameterReflection(
			%s,
			%s,
			%s,
			%s,
			%s
		),%s',
				$this->export($parameterSignature->getName()),
				$this->export($parameterSignature->isOptional()),
				$this->export($parameterSignature->getType()),
				$this->export($parameterSignature->isPassedByReference()),
				$this->export($parameterSignature->isVariadic()),
				"\n"
			);
		}

		$functionDump = sprintf(
			'return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	%s,
	[
%s
	],
	%s,
	%s
);',
			$this->export($functionName),
			$parametersDump,
			$this->export($functionSignature->isVariadic()),
			$this->export($functionSignature->getReturnType())
		);
		return sprintf(
			'<?php declare(strict_types = 1);%s%s%s',
			"\n\n",
			$functionDump,
			"\n"
		);
	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	private function export($value): string
	{
		$exported = var_export($value, true);
		$exported = str_replace("\n", ' ', $exported);
		$exported = str_replace('( )', '()', $exported);

		return $exported;
	}

}
