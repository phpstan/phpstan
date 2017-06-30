<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

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

	/**
	 * @param mixed[] $sureTypes
	 * @param mixed[] $sureNotTypes
	 */
	public function __construct(array $sureTypes = [], array $sureNotTypes = [])
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

	public function intersectWith(SpecifiedTypes $other): self
	{
		$sureTypeUnion = [];
		$sureNotTypeUnion = [];

		foreach ($this->sureTypes as $exprString => list($exprNode, $type)) {
			if (isset($other->sureTypes[$exprString])) {
				$sureTypeUnion[$exprString] = [
					$exprNode,
					$type->combineWith($other->sureTypes[$exprString][1]),
				];
			}
		}

		return new self($sureTypeUnion, $sureNotTypeUnion);
	}


	public function unionWith(SpecifiedTypes $other): self
	{
		$sureTypeUnion = $this->sureTypes + $other->sureTypes;
		$sureNotTypeUnion = $this->sureNotTypes + $other->sureNotTypes;

		foreach ($this->sureNotTypes as $exprString => list($exprNode, $type)) {
			if (isset($other->sureNotTypes[$exprString])) {
				$sureNotTypeUnion[$exprString] = [
					$exprNode,
					$type->combineWith($other->sureNotTypes[$exprString][1]),
				];
			}
		}

		return new self($sureTypeUnion, $sureNotTypeUnion);
	}

}
