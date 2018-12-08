<?php declare(strict_types = 1);

namespace PHPStan\Rules;

interface LineRuleError extends RuleError
{

	public function getLine(): int;

}
