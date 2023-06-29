<?php

class MyApp  {
	public function __construct(HttpResponse  $response) {
		$response->deleteCookie('foo');
	}
}
