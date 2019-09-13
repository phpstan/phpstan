<?php declare(strict_types = 1);

namespace x {
	class boo {}
}

namespace y {
	use x\{boo, baz};

	class x {
		/** @var \x\boo */
		private $boo;

		/** @var \x\baz */
		private $baz;
		public function __construct(boo $boo, baz $baz) {
			$this->boo = $boo;
			$this->baz = $baz;
		}
	}
}
