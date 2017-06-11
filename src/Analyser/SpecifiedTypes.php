<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node;
use PHPStan\Type\Type;

class SpecifiedTypes
{

	/**
	 * @var mixed[]
	 */
	private $sureTypes;

	/**
	 * @var mixed[]
	 */
	private $sureNotTypes;

	public function __construct(
		array $sureTypes = [],
		array $sureNotTypes = []
	)
	{
		$this->sureTypes = $sureTypes;
		$this->sureNotTypes = $sureNotTypes;
	}

	/**
	 * @return mixed[]
	 */
	public function getSureTypes(): array
	{
		return $this->sureTypes;
	}

	/**
	 * @return mixed[]
	 */
	public function getSureNotTypes(): array
	{
		return $this->sureNotTypes;
	}

	public function addSureType(Node $expr, string $exprString, Type $type): self
	{
		$types = $this->sureTypes;
		if (isset($types[$exprString])) {
			unset($types[$exprString]); // because we don't have intersection types
			return new self($types, $this->sureNotTypes);
		}

		$types[$exprString] = [
			$expr,
			$type,
		];

		return new self(
			$types,
			$this->sureNotTypes
		);
	}

	public function addSureNotType(Node $expr, string $exprString, Type $type): self
	{
		$types = $this->sureNotTypes;
		if (isset($types[$exprString])) {
			$type = $types[$exprString][1]->combineWith($type);
		}

		$types[$exprString] = [
			$expr,
			$type,
		];

		return new self(
			$this->sureTypes,
			$types
		);
	}

	public function hasVariableSureType(string $variableName): bool
	{
		$exprString = sprintf('$%s', $variableName);
		return isset($this->sureNotTypes[$exprString]);
	}

	public function unionWith(SpecifiedTypes $b): self
	{
		$sureTypeUnion = [];
		$sureNotTypeUnion = [];

		foreach ($this->sureTypes as $sureTypeString => list($exprNode, $type)) {
			if (isset($b->sureTypes[$sureTypeString])) {
				$sureTypeUnion[$sureTypeString] = [
					$exprNode,
					$type->combineWith($b->sureTypes[$sureTypeString][1]),
				];
			}
		}

		return new self($sureTypeUnion, $sureNotTypeUnion);
	}

}
