<?php

namespace CallMethodsNullIssue;

class Foo
{

	/**
	 * @return int|null
	 */
	public function lorem()
	{

	}

	/**
	 * @return int|null
	 */
	public function ipsum()
	{

	}

}

class Bar
{

	/**
	 * @return int|null
	 */
	public function lorem()
	{

	}

	/**
	 * @return int|null
	 */
	public function ipsum()
	{

	}

}

class Baz
{

	/**
	 * @return Foo|null
	 */
	public function getFoo(): Foo
	{

	}

	/**
	 * @return Bar|null
	 */
	public function getBar(): Bar
	{

	}

	public function process(): string
	{
		if ($this->getFoo() === null && $this->getBar() === null) {
			return '';
		}
		if ($this->getFoo() !== null && $this->getBar() === null) {
			return '';
		} elseif ($this->getBar() !== null && $this->getFoo() === null) {
			return '';
		}

		$foo = $this->getFoo();
		$bar = $this->getBar();
		if ($bar->lorem() !== null && $foo->lorem() === null) {
			return '';
		} elseif ($bar->lorem() === null && $foo->lorem() !== null) {
			return '';
		} elseif ($bar->lorem() !== null && $foo->lorem() !== null) {
			return $bar->lorem() > $foo->lorem() ? '' : '';
		}
		if ($foo->ipsum() < $bar->ipsum()) {
			return '';
		}

		return '';
	}

}

class Dolor
{
	public function getTime(): \DateTimeImmutable
	{
		return new \DateTimeImmutable();
	}

	public function process(): void
	{
		if (true && $this->getTime() === null) {
			// nothing

		} elseif (false) {
			// nothing
		}

		$this->getTime()->getTimestamp();
	}
}
