<?php

// Test comment
new class () {

	public function doFoo(): void
	{
		$this->doBar();
	}

};

/* Test comment */
new class () {

	public function doFoo(): void
	{
		$this->doBar();
	}

};

/** Test comment */
new class () {

	public function doFoo(): void
	{
		$this->doBar();
	}

};
