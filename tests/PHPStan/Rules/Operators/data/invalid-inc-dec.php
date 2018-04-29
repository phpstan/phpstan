<?php

namespace InvalidIncDec;

function ($a) {
	$a++;

	$b = [1];
	$b[0]++;

	date('j. n. Y')++;
	date('j. n. Y')--;
};
