<?php

class defaultActions
{
	public function executePublic(): void
	{
		$this->foo();
	}

	protected function foo(): void
	{
	}
}
