<?php

class HttpResponse
{
	/**
	 * @param string      $name    The name of the cookie
	 * @param string|null $value   the value of the cookie, a empty value to delete the cookie
	 * @param array       $options Different cookie Options. Supported keys are:
	 *                             "expires" int|string|\DateTimeInterface The time the cookie expires
	 *                             "path" string                           The path on the server in which the cookie will be available on
	 *                             "domain" string|null                    The domain that the cookie is available to
	 *                             "secure" bool                           Whether the cookie should only be transmitted over a secure HTTPS connection from the client
	 *                             "httponly" bool                         Whether the cookie will be made accessible only through the HTTP protocol
	 *                             "samesite" string|null                  Whether the cookie will be available for cross-site requests
	 *                             "raw" bool                              Whether the cookie value should be sent with no url encoding
	 *
	 * @throws \InvalidArgumentException
	 */
	public function sendCookie($name, $value, array $options = [])
	{
	}

	public function deleteCookie(string $name)
	{
		$this->sendCookie($name, '');
	}
}
