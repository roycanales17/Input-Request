<?php

	namespace App\Headers;

	abstract class Properties
	{
		private static array $data = [
			'post' => [],
			'json' => [],
			'server' => [],
			'files' => [],
			'cookies' => [],
			'headers' => [],
			'query' => [],
			'raw_input' => [],
			'xml' => [],
			'session' => []
		];

		public static function capture(): void
		{
			self::processSuperGlobals();
			self::processRawInput();
			self::processFiles();
		}

		private static function processSuperGlobals(): void
		{
			$sources = [
				'query' => $_GET,
				'post' => $_POST,
				'server' => $_SERVER,
				'cookies' => $_COOKIE,
				'headers' => getallheaders(),
				'session' => $_SESSION ?? []
			];

			foreach ($sources as $type => $data) {
				foreach ($data as $key => $value) {
					self::register($type, $key, $value);
				}
			}
		}

		private static function processRawInput(): void
		{
			$rawInput = file_get_contents('php://input');
			if (!$rawInput) return;

			if (str_contains($rawInput, '<') && ($xmlData = simplexml_load_string($rawInput))) {
				foreach ($xmlData as $key => $value) {
					self::register('xml', $key, (string)$value);
				}
			} elseif (preg_match('/^[a-zA-Z0-9\s]*$/', $rawInput)) {
				self::register('raw_input', 'plain_text', $rawInput);
			} else {
				self::register('raw_input', 'raw_input', $rawInput);
			}

			if ($jsonPayload = json_decode($rawInput, true)) {
				foreach ($jsonPayload as $key => $value) {
					self::register('json', $key, $value);
				}
			}
		}

		private static function processFiles(): void
		{
			foreach ($_FILES as $key => $attr) {
				if (isset($attr['name']) && is_array($attr['name'])) {
					$files = [];
					foreach ($attr['name'] as $index => $name) {
						if (trim($name)) {
							$files[] = self::formatFileAttributes($attr, $index);
						}
					}
					self::register('files', $key, $files);
				} elseif (!empty($attr['name'])) {
					self::register('files', $key, self::formatFileAttributes($attr));
				}
			}
		}

		private static function formatFileAttributes(array $attr, ?int $index = null): array
		{
			return [
				'name' => $index !== null ? $attr['name'][$index] : $attr['name'],
				'full_path' => $index !== null ? $attr['full_path'][$index] : $attr['full_path'],
				'type' => $index !== null ? $attr['type'][$index] : $attr['type'],
				'tmp_name' => $index !== null ? $attr['tmp_name'][$index] : $attr['tmp_name'],
				'error' => $index !== null ? $attr['error'][$index] : $attr['error'],
				'size' => $index !== null ? $attr['size'][$index] : $attr['size']
			];
		}

		protected static function fetch(string $property): array
		{
			return self::$data[$property] ?? [];
		}

		protected static function register(string $property, string $key, mixed $value): void
		{
			if (isset(self::$data[$property])) {
				self::$data[$property][$key] = $value;
			}
		}
	}