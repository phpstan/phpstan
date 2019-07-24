<?php

sprintf($message, 'foo'); // skip - format not a literal string
sprintf('%s', 'foo'); // ok
sprintf('%s %% %% %s', 'foo', 'bar'); // ok
sprintf('%s %s', 'foo'); // one parameter missing
sprintf('foo', 'foo'); // one parameter over
sprintf('foo %s', 'foo', 'bar'); // one parameter over
sprintf('%2$s %1$s %% %1$s %%%', 'one'); // one parameter missing
sprintf('%2$s %%'); // two parameters required
sprintf('%2$s %1$s %1$s %s %s %s %s'); // four parameters required
sprintf('%2$s %1$s %% %s %s %s %s %%% %%%%', 'one', 'two', 'three', 'four'); // ok
sprintf("%'.9d %1$'.9d %0.3f %d %d %d", 123, 456);
sprintf('%-4s', 'foo'); // ok
sprintf('%%s %s', 'foo', 'bar'); // one parameter over
sprintf('https://%s/staticmaps/%dx%d/%d/%Fx%F.png'); // six parameters required
sprintf('%%0%dd%%0%dd'); // two parameters required
sprintf('%%%s%%'); // one required
sscanf($str, '%d%d'); // correct - result returned as an array
sscanf($str, '%d', $number); // correct
sscanf($str, '%d%d', $number); // one parameter missing
sscanf($str, '%20[^,],%d', $string, $number); // ok
fscanf($resource, '%d%d'); // correct - result returned as an array
fscanf($resource, '%d', $number); // correct
fscanf($resource, '%d%d', $number); // one parameter missing
sprintf('%+02d', 1); // ok
sprintf('%+02d %d', 1); // one parameter missing
sprintf('%-02d', 1); // ok
sprintf('%-02d %d', 1); // one parameter missing
sprintf('<info>% -20s</info> : %s', 'id', 42); // ok
sprintf('%-s', 'x'); // ok
sprintf('%%s'); // ok
sscanf($str, "%20[^\n]\n%d", $string, $number); // ok
sscanf($str, "%20[^\n]\r\n%d", $string, $number); // ok
sscanf($str, "%20[^abcde]a%d", $string, $number); // ok
printf("%.E", 3.14159); // ok
sprintf("%.E", 3.14159); // ok
sscanf($str, '%.E', $number); // ok
fscanf($str, '%.E', $number); // ok
sscanf($str, '%[A-Z]%d', $char, $number); // ok
sprintf('%s %s %s', ...[1]); // do not detect unpacked arguments
sprintf('%s %s %s', ...[1, 2, 3]); // ok

$format = '%s %s';
sprintf($format, 'foo'); // one parameter missing
sprintf($format, 'foo', 'bar'); // ok

$variousPlaceholderCount = 'foo';
if (rand(0, 1) === 0) {
	$variousPlaceholderCount = 'foo %s %s';
}
sprintf($variousPlaceholderCount, 'bar');
sprintf($variousPlaceholderCount, 'bar', 'baz');
sprintf($variousPlaceholderCount, 'bar', 'baz', 'lorem');
