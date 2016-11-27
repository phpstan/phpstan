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
			$type = $types[$exprString][1]->combineWith($type);
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

}
