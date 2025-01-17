<?php

	namespace App\Headers;

	use App\Headers\Requests\Headers;
	use App\Headers\Requests\Inputs;
	use App\Headers\Scheme\Validate;

	class Request extends Properties
	{
		use Inputs;
		use Headers;

		public function validate(array $config): Validate
		{
			return new Validate($config);
		}

		public function response(mixed $content = '', int $status = 200, array $headers = []): Response
		{
			return new Response($content, $status, $headers);
		}
	}