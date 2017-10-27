<?php

if ($a ?? false) {
	echo $a; // should be OK
}

if ($b ?? true) {
	echo $b; // should report undefined $b
}
