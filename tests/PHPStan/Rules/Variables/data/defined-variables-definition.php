<?php

namespace DefinedVariables;

function &refFunction() {
	$obj = new \stdClass();
	return $obj;
};
