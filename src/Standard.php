<?php

	use App\Headers\Redirect;
	use App\Headers\Request;
	use App\Headers\Response;

	function request(): Request
	{
		return new Request();
	}

	function response(mixed $content = '', int $status = 200, array $headers = []): Response
	{
		return new Response($content, $status, $headers);
	}

	function redirect(string $url, int $status = 302, array $headers = []): Redirect
	{
		return response(status: $status, headers: $headers)->redirect($url);
	}