<?php // lint >= 7.1

namespace CatchUnion;

class FooException extends \Exception
{

}

class BarException extends \Exception
{

}

function () {
	try {

	} catch (FooException | BarException $e) {
		die;
	}
};
