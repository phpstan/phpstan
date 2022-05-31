<?php

class MyApp extends ApplicationController {
	protected $warengruppenid = [
		ExternalClass::WARENGRUPPE_STOCKSCHIRME,
		ExternalClass::WARENGRUPPE_TASCHENSCHIRME,
	];

	public function __construct() {
	}
}
