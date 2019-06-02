<?php declare(strict_types=1);

namespace IgnoreErrorComments;

class Stub
{

	/**
	 * Ignore rule inside whole method
	 *
	 * @phpstan-ignore-message Fail.
	 */
	public function wholeMethodIgnored()
	{
		doFoo();

		doFoo();
	}

	/**
	 * Ignore some rules with inline comments
	 */
	public function someNodesIgnored()
	{

		// @phpstan-ignore-next-line
		doFoo();
		doBar();

		doFoo();

		/** @phpstan-ignore-message-regexp ^Fai[a-z]\.$ */
		doFoo();

		/** @phpstan-ignore-message-regex ^Fai[a-z]\.$ */
		doFoo();

		/* @phpstan-ignore-message-regex ^Fai[a-z]\.$ */
		doFoo();

		/** @phpstan-ignore-next-line */
		echo 'Foo'; // no error reported

		/* @phpstan-ignore-message-regex ^Fai[a-z\.$ */
		doFoo();

		/** @phpstan-ignore-message Test */
		echo 'Foo';

		/** @phpstan-ignore-message-regex ^Test$ */
		echo 'Foo';

		/** @phpstan-ignore-message Test */
		if (true) {

		}

	}

}

/**
 * Ignore rules inside whole class
 *
 * @phpstan-ignore-next-line
 */
class IgnoredClassStub
{
	public function run()
	{
		doFoo();

		doFoo();
	}
}
