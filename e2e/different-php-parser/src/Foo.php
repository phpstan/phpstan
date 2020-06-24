<?php

namespace App;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Catch_;

class Foo
{

	public function doFoo(Catch_ $catch_): void
	{
		$this->doBar($catch_->var);
	}

	public function doBar(Variable $var): void
	{

	}

}
