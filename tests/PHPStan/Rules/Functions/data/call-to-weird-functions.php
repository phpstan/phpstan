<?php

implode(); // should report 1-2 parameters
implode('-', []); // OK
implode([]); // also OK , variant with $pieces only
implode('-', [], 'foo'); // should report 3 parameters given, 1-2 required

strtok(); // should report 1-2 parameters
strtok('/something', '/'); // OK
strtok('/'); // also OK, variant with $token only
strtok('/something', '/', 'foo'); // should report 3 parameters given, 1-2 required

fputcsv($handle);
fputcsv($handle, $data, ',', '""', '\\');

imagepng(); // should report 1-4 parameters
imagepng('resource'); // OK
imagepng('resource', 'to', 1, 2); // OK
imagepng('resource', 'to', 1, 2, 4); // should report 5 parameters given, 1-4 required

session_start([
	'name' => '',
	'cookie_path' => '',
	'cookie_secure' => '',
	'cookie_domain' => '',
]);

locale_get_display_language('cs_CZ'); // OK
locale_get_display_language('cs_CZ', 'en'); // OK
locale_get_display_language('cs_CZ', 'en', 'foo'); // should report 3 parameters given, 1-2 required

mysqli_fetch_all(); // should report 1-2 parameters
mysqli_fetch_all(new mysqli_result()); // OK
mysqli_fetch_all(new mysqli_result(), MYSQLI_ASSOC); // OK
mysqli_fetch_all(new mysqli_result(), MYSQLI_ASSOC, true); // should report 3 parameters given, 1-2 required
