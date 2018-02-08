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
$resource = imagecreatefrompng('filename');
imagepng(); // should report 1-4 parameters
imagepng($resource); // OK
imagepng($resource, 'to', 1, 2); // OK
imagepng($resource, 'to', 1, 2, 4); // should report 5 parameters given, 1-4 required

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
openssl_open('', $open, '', openssl_get_privatekey('')); // OK
openssl_open('', $open, '', openssl_get_privatekey(''), 'foo', 'bar', 'baz'); // should report 7 parameters, 4-6 required.

openssl_x509_parse('foo'); // OK
openssl_x509_parse('foo', true); // OK
openssl_x509_parse('foo', true, 'bar'); // should report 3 parameters given, 1-2 required

get_defined_functions(); // OK for PHP <7.1.10

openssl_pkcs12_export('signed-csr', $output, 'private-key', 'password'); // OK
openssl_pkcs12_export('signed-csr', $output, 'private-key', 'password', ['friendlyname' => 'name']); // OK
openssl_pkcs12_export('signed-csr', $output, 'private-key', 'password', ['friendlyname' => 'name'], 'bar');  // should report 6 parameters given, 4-5 required