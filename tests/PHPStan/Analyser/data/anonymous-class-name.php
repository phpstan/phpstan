<?php

namespace AnonymousClassName;

function () {
	$foo = new class () {

		/** @var Foo */
		public $fooProperty;

		/**
		 * @return Foo
		 */
		public function doFoo()
		{
			'inside';
		}
	};

	'outside';
};
