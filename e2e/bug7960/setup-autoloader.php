<?php

spl_autoload_register(function (string $name) {
    if (strpos($name, 'Repro_7960_') === 0) {
        $nameExploded = explode('__extends_', $name, 2);
        eval('class ' . $name . (count($nameExploded) > 1 ? ' extends ' . $nameExploded[1] : '') . ' {
            public function save(): void
            {
            }
        }');
    }
});
