<?php declare(strict_types=1);

namespace IgnoreErrorComments;

class Stub
{

	/**
	 * Ignore rule inside whole method
	 *
	 * @phpstan-ignore-message Fail.
	 */
	public function wholeMethodIgnored(): int
	{
		echo $baz;

		return 'wrong type';
	}

	/**
	 * Ignore some rules with inline comments
	 */
	public function someNodesIgnored()
	{

		// @phpstan-ignore-next-line
		doFoo();
		doBar();

		echo 'This is valid.';

		/** @phpstan-ignore-message-regexp ^Fai[a-z]\.$ */
		echo $ignoreWithDocBlock;

	}

}

/**
 * Ignore rules inside whole class
 *
 * @phpstan-ignore-next-line
 */
class IgnoredClassStub
{
	public function run(): int
	{
		echo $baz;

		return 'wrong type';
	}
}
