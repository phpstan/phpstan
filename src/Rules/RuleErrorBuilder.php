<?php declare(strict_types = 1);

namespace PHPStan\Rules;

class RuleErrorBuilder
{

	/** @var string */
	private $message;

	private function __construct()
	{
	}

	public static function message(string $message): self
	{
		$self = new self();
		$self->message = $message;

		return $self;
	}

	public function build(): RuleError
	{
		$message = $this->message;
		return new class ($message) implements RuleError {

			/** @var string */
			private $message;

			public function __construct(string $message)
			{
				$this->message = $message;
			}

			public function getMessage(): string
			{
				return $this->message;
			}

		};
	}

}
