<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class UndefinedVariableException extends \PHPStan\AnalysedCodeException
{

	/** @var \PHPStan\Analyser\Scope */
	private $scope;

	/** @var string */
	private $variableName;

	public function __construct(Scope $scope, string $variableName)
	{
		parent::__construct(sprintf('Undefined variable: $%s', $variableName));
		$this->scope = $scope;
		$this->variableName = $variableName;
	}

	public function getScope(): Scope
	{
		return $this->scope;
	}

	public function getVariableName(): string
	{
		return $this->variableName;
	}

}
