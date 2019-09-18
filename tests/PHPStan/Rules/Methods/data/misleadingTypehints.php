<?php

class FooWithoutNamespace
{

	public function misleadingBoolReturnType(): boolean
	{
		if (rand(0, 1)) {
			return true;
		}

		if (rand(0, 1)) {
			return 1;
		}

		if (rand(0, 1)) {
			return new boolean();
		}
	}

	public function misleadingIntReturnType(): integer
	{
		if (rand(0, 1)) {
			return 1;
		}

		if (rand(0, 1)) {
			return true;
		}

		if (rand(0, 1)) {
			return new integer();
		}
	}

	public function misleadingMixedReturnType(): mixed
	{
		if (rand(0, 1)) {
			return 1;
		}

		if (rand(0, 1)) {
			return true;
		}

		if (rand(0, 1)) {
			return new mixed();
		}
	}

}
