<?php declare(strict_types = 1);

namespace PHPStan\Rules;

class RuleErrorBuilder
{

	/** @var string */
	private $message;

	/** @var int|null */
	private $line;

	private function __construct()
	{
	}

	public static function message(string $message): self
	{
		$self = new self();
		$self->message = $message;

		return $self;
	}

	public function line(int $line): self
	{
		$this->line = $line;

		return $this;
	}

	public function build(): RuleError
	{
		$interfaces = ['\\PHPStan\\Rules\\RuleError'];
		$methods = [sprintf('public function getMessage(): string { return %s; }', var_export($this->message, true))];
		if ($this->line !== null) {
			$interfaces[] = '\\PHPStan\Rules\LineRuleError';
			$methods[] = sprintf('public function getLine(): int { return %s; }', var_export($this->line, true));
		}

		$className = 'RuleError' . sha1(uniqid());
		$class = sprintf(
			'class %s implements %s { %s };',
			$className,
			implode(', ', $interfaces),
			implode("\n\n", $methods)
		);

		eval($class);

		return new $className();
	}

}
