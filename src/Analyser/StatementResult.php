<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

use PhpParser\Node\Stmt;

class StatementResult
{

	/** @var Scope */
	private $scope;

	/** @var Stmt[] */
	private $alwaysTerminatingStatements;

	/** @var StatementExitPoint[] */
	private $exitPoints;

	/**
	 * @param Scope $scope
	 * @param Stmt[] $alwaysTerminatingStatements
	 * @param StatementExitPoint[] $exitPoints
	 */
	public function __construct(
		Scope $scope,
		array $alwaysTerminatingStatements,
		array $exitPoints
	)
	{
		$this->scope = $scope;
		$this->alwaysTerminatingStatements = $alwaysTerminatingStatements;
		$this->exitPoints = $exitPoints;
	}

	public function getScope(): Scope
	{
		return $this->scope;
	}

	/**
	 * @return Stmt[]
	 */
	public function getAlwaysTerminatingStatements(): array
	{
		return $this->alwaysTerminatingStatements;
	}

	public function areAllAlwaysTerminatingStatementsLoopTerminationStatements(): bool
	{
		if (count($this->alwaysTerminatingStatements) === 0) {
			return false;
		}

		foreach ($this->alwaysTerminatingStatements as $statement) {
			if ($statement instanceof Stmt\Break_) {
				continue;
			}
			if ($statement instanceof Stmt\Continue_) {
				continue;
			}

			return false;
		}

		return true;
	}

	public function isAlwaysTerminating(): bool
	{
		return count($this->alwaysTerminatingStatements) > 0;
	}

	public function filterOutLoopTerminationStatements(): self
	{
		foreach ($this->alwaysTerminatingStatements as $statement) {
			if ($statement instanceof Stmt\Break_ || $statement instanceof Stmt\Continue_) {
				return new self($this->scope, [], $this->exitPoints);
			}
		}

		return $this;
	}

	/**
	 * @return StatementExitPoint[]
	 */
	public function getExitPoints(): array
	{
		return $this->exitPoints;
	}

	/**
	 * @param string $stmtClass
	 * @return StatementExitPoint[]
	 */
	public function getExitPointsByType(string $stmtClass): array
	{
		$exitPoints = [];
		foreach ($this->exitPoints as $exitPoint) {
			if (!$exitPoint->getStatement() instanceof $stmtClass) {
				continue;
			}

			$exitPoints[] = $exitPoint;
		}

		return $exitPoints;
	}

}
