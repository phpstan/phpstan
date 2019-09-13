<?php

namespace AnonymousClassWrongFilename;

class Foo
{

	public function doFoo(): void
	{
		$foo = new class {

			/**
			 * @param self $test
			 * @return Bar
			 */
			public function doBar($test): Bar
			{
				return new Bar();
			}

		};

		$bar = $foo->doBar($this);
		$bar->test();
	}

}
