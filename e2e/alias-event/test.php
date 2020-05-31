<?php

class MyOwnClass
{

	public function doFoo(): void
	{

	}

}

function (\Event $e): void {
	$e->doFoo();
};
