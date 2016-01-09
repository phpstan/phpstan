<?php declare(strict_types = 1);

namespace PHPStan\Parser;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;

class FunctionCallStatementFinder
{

	/**
	 * @param string $functionName
	 * @param mixed $statements
	 * @return \PhpParser\Node|null
	 */
	public function findFunctionCallInStatements(string $functionName, $statements)
	{
		foreach ($statements as $statement) {
			if (is_array($statement)) {
				$result = $this->findFunctionCallInStatements($functionName, $statement);
				if ($result !== null) {
					return $result;
				}
			}

			if (!($statement instanceof \PhpParser\Node)) {
				continue;
			}

			if ($statement instanceof FuncCall && $statement->name instanceof Name) {
				if ($functionName === (string) $statement->name) {
					return $statement;
				}
			}

			$result = $this->findFunctionCallInStatements($functionName, $statement);
			if ($result !== null) {
				return $result;
			}
		}

		return null;
	}

}
