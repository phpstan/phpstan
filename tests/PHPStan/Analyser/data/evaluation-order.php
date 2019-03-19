<?php

function () {
	six(
		'one',
		five(
			'two',
			'three',
			four()
		)
	);
};
