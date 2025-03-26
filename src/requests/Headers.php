<?php

	namespace App\Headers\Requests;

	use App\Headers\Scheme\Headers as BaseHeaders;

	trait Headers
	{
		private static array $headersCache = [];

		public function requestHeaders(): BaseHeaders
		{
			return new BaseHeaders(self::$headersCache ?: self::$headersCache = self::fetch('headers'));
		}

		public static function headers(): array
		{
			return (new self)->requestHeaders()->all();
		}

		public static function header(string $header): mixed
		{
			return (new self)->requestHeaders()->header($header);
		}

		public static function method(): string
		{
			return (new self)->requestHeaders()->method();
		}
	}