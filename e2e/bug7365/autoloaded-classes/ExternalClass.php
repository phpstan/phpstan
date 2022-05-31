<?php

class ExternalClass {
	const WARENGRUPPE_STOCKSCHIRME = ClassWithConstants::WARENGRUPPE_STOCKSCHIRME;
	const WARENGRUPPE_TASCHENSCHIRME = ClassWithConstants::WARENGRUPPE_TASCHENSCHIRME;
	const WARENGRUPPE_SONNENSCHIRME = ClassWithConstants::WARENGRUPPE_SONNENSCHIRME;

	public function doSomething() {
		echo "ExternalClass::doSomething()\n";
	}
}
