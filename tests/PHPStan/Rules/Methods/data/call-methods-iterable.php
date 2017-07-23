<?php // lint >= 7.1

namespace CallMethodsIterableNotCheckingTypeIssue;

class Uuid
{

	/**
	 * @param Uuid[] $ids
	 */
	public function bar(iterable $ids)
	{
		$id = new self();
		$id->bar([null]);

	}
}
