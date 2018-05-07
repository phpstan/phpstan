<?php

namespace AnonymousClassName;

function () {
	$foo = new class () {
		public function doFoo()
		{
			'inside';
		}
	};

	'outside';
};
