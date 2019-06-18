<?php

namespace TooWidePrivateMethodReturnType;

class Foo
{

	private function foo(): \Generator {
		yield 1;
		yield 2;
		return 3;
	}

	private function bar(): ?string {
		return null;
	}

	private function baz(): ?string {
		return 'foo';
	}

	private function lorem(): ?string {
		if (rand(0, 1)) {
			return '1';
		}

		return null;
	}

	public function ipsum(): ?string {
		return null;
	}

}
