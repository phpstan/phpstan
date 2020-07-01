<?php

namespace App;

class ShouldWork extends \PhpParser\NodeTraverser
{
	protected function traverseArray(array $nodes)
	{
		return [];
	}
}
