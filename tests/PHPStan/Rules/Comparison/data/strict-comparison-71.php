<?php // lint >= 7.1

namespace StrictComparison71;

class Foo
{

	public function returnsNullableString(): ?bool
	{
		return false;
	}

	public function doCheckNullableString(): int
	{
		if ($this->returnsNullableString() === true) {
			return 1;
		} else if ($this->returnsNullableString() === false) {
			return 2;
		} else if ($this->returnsNullableString() === null) {
			return 3;
		}
		return 4;
	}

}
