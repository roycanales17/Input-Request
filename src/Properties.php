<?php

	namespace App\Headers;

	abstract class Properties
	{
		private static array $post = [];
		private static array $json = [];
		private static array $server = [];
		private static array $files = [];
		private static array $cookies = [];
		private static array $headers = [];
		private static array $query = [];
		private static array $raw_input = [];
		private static array $xml = [];
		private static array $session = [];

		public static function capture(): void
		{
			$dataResources = [
				'query' => $_GET,
				'post' => $_POST,
				'server' => $_SERVER,
				'cookie' => $_COOKIE,
				'headers' => getallheaders(),
				'session' => $_SESSION ?? []
			];

			foreach ($dataResources as $type => $data) {
				foreach ($data as $key => $value) {
					self::register($key, $value, $type);
				}
			}

			$rawInput = file_get_contents('php://input');
			if ($rawInput) {
				if (str_contains($rawInput, '<') && $xmlData = simplexml_load_string($rawInput)) {
					foreach ($xmlData as $key => $value) {
						self::register($key, (string)$value, 'xml');
					}
				} elseif (preg_match('/^[a-zA-Z0-9\s]*$/', $rawInput)) {
					self::register('plain_text', $rawInput, 'raw_input');
				} else {
					self::register('raw_input', $rawInput, 'raw_input');
				}
			}

			$jsonPayload = json_decode($rawInput, true);
			if ($jsonPayload) {
				foreach ($jsonPayload as $key => $value) {
					self::register($key, $value, 'json');
				}
			}

			$indexedFiles = [];
			foreach ($_FILES as $key => $attr) {

				if (isset($attr[ 'name' ]) && is_array($attr[ 'name' ])) {
					if (!isset($indexedFiles[$key]))
						$indexedFiles[$key] = [];

					foreach ($attr[ 'name' ] as $index => $name) {
						if (trim($name ?? '')) {
							$indexedFiles[$key][] = [
								'name' => $name,
								'full_path' => $attr[ 'full_path' ][ $index ],
								'type' => $attr[ 'type' ][ $index ],
								'tmp_name' => $attr[ 'tmp_name' ][ $index ],
								'error' => $attr[ 'error' ][ $index ],
								'size' => $attr[ 'size' ][ $index ]
							];
						}
					}
				} else {
					if (!empty($attr[ 'name' ])) {
						$indexedFiles[$key] = [
							'name' => $attr[ 'name' ],
							'full_path' => $attr[ 'full_path' ],
							'type' => $attr[ 'type' ],
							'tmp_name' => $attr[ 'tmp_name' ],
							'error' => $attr[ 'error' ],
							'size' => $attr[ 'size' ]
						];
					}
				}
			}

			foreach ($indexedFiles as $file => $files) {
				self::register($file, $files, 'files');
			}
		}

		protected static function fetch(string $property): array
		{
			if (!isset(self::$$property))
				return [];

			return self::$$property;
		}

		protected static function register(string $key, mixed $value, string $property): void
		{
			$property = strtolower($property);
			if (isset(self::$$property)) {
				self::$$property[$key] = $value;
			}
		}
	}