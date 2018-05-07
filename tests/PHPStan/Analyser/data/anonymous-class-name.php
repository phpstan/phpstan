<?php

namespace AnonymousClassName;

function () {
	$foo = new class () {

		/** @var Foo */
		public $fooProperty;

		public function doFoo()
		{
			'inside';
		}
	};

	'outside';
};
