<?php
$mem_result = "MemTotal:        8136936 kB";
$stat['mem_total'] = round(preg_replace("#[^0-9]+(?:\.[0-9]*)?#", "", $mem_result) / 1024 / 1024, 3);
