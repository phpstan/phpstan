<?php

namespace MissingReturn;

class Foo
{

	public function doFoo(): int
	{

	}

	public function doBar(): int
	{
		// noop comment
	}

	public function doBaz(): int
	{
		doFoo();
		doBar();
	}

	public function doLorem(): int
	{
		if (doFoo()) {

		}

		try {

		} catch (\Exception $e) {

		}

		if (doFoo()) {
			doFoo();
			doBar();
			if (doFoo()) {

			} elseif (blabla()) {

			} else {

			}
		} else {
			try {

			} catch (\Exception $e) {

			}
		}
	}

}

class Bar
{

	public function doFoo(): int
	{
		return 1;
	}

	public function doBar(): int
	{
		doFoo();

		return 1;
	}

	public function doBaz(): void
	{

	}

	public function doLorem(): void
	{
		doFoo();
	}

	public function doIpsum()
	{

	}

	public function doDolor()
	{
		doFoo();
	}

	public function doSit(): iterable
	{
		doBar();
		doFoo(yield 1);
	}

}

class Baz
{

	public function doFoo()
	{
		function (): int {

		};
	}

}

function doFoo(): int
{

}

class Yielding
{

	public function doFoo(bool $bool): iterable
	{
		while ($bool) {
			yield 1;
		}
	}

}

class SwitchBranches
{

	public function doFoo(int $i): int
	{
		switch ($i) {
			case 0:
			case 1:
			case 2:
				return 1;
			default:
				return 2;
		}
	}

	public function doBar(int $i): int
	{
		switch ($i) {
			case 0:
				return 0;
			case 1:
				return 1;
			case 2:
				return 2;
		}
	}

	public function doBaz(int $i): int
	{
		switch ($i) {
			case 0:
				return 0;
			case 1:
				return 1;
			case 2:
				return 2;
			default:
				return 3;
		}
	}

	public function doLorem(int $i): int
	{
		switch ($i) {
			case 0:
			case 1:
			case 2:
				return 1;
		}
	}

	public function doIpsum(int $i): int
	{
		switch ($i) {
			case 0:
				return 1;
			case 1:
			case 2:
			default:
		}
	}

	public function doDolor(int $i): int
	{
		switch ($i) {
			case 0:
				return 1;
			case 1:
			case 2:
		}
	}

}

class TryCatchFinally
{

	public function doFoo(): int
	{
		try {

		} catch (\Exception $e) {

		} catch (\Throwable $e) {

		} finally {
			return 1;
		}
	}

	public function doBar(): int
	{
		try {
			return 1;
		} catch (\Exception $e) {
			return 1;
		} catch (\Throwable $e) {
			return 1;
		} finally {

		}
	}

	public function doBaz(): int
	{
		try {
			return 1;
		} catch (\Exception $e) {
			return 1;
		} catch (\Throwable $e) {

		}
	}

	public function doLorem(): int
	{
		try {
			return 1;
		} finally {

		}
	}

	public function doIpsum(): int
	{
		try {

		} finally {
			return 1;
		}
	}

	public function doDolor(): int
	{
		try {

		} finally {

		}
	}

}

class MoreYielding
{

	public function doFoo(bool $foo): iterable
	{
		if ($foo) {
			yield 1;
		}
	}

}

class ReturnInPhpDoc
{

	/**
	 * @return int
	 */
	public function doFoo()
	{

	}

}

class YieldInAssign
{

	public function doFoo(\Generator $items): \Generator
	{
		while ($items->valid()) {
			$item = $items->current();

			$state = yield $item;

			$items->send($state);
		}
	}

}
