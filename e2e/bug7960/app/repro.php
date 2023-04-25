<?php

// Repro_7960_xxx classes are autoloaded in setup-autoloader.php
class Foo extends Repro_7960_A__extends_Repro_7960_ABase
{
    public Repro_7960_B $objB;
    
    /** @var \Repro_7960_C */
    public $objC;

    public Repro_7960_D__extends_Repro_7960_DBase $objD;
    
    /** @var \Repro_7960_E__extends_Repro_7960_EBase */
    public $objE;
    
    public Repro_7960_F__extends_Repro_7960_Fx__extends_Repro_7960_Fy $objF;
    
    /** @var \Repro_7960_G__extends_Repro_7960_Gx__extends_Repro_7960_Gy */
    public Repro_7960_Gx__extends_Repro_7960_Gy $objG;
}

$foo = new Foo();
$foo->save();
