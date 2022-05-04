<?php

declare(strict_types=1);

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class LoginController
{
	public function login(): JsonResponse
	{
		return new JWTAuthenticationFailureResponse();
	}
}
