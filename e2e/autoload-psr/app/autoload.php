<?php

set_include_path(get_include_path() . ':' . __DIR__ . '/../libs/');

autoload_register_psr4_prefix('Foo\Bar', 'Foo/');
