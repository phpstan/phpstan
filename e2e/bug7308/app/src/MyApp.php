<?php

class MyApp extends ApplicationController {
	public function __construct(ExternalClass  $ext) {
		$ext->doSomething();
	}
}
