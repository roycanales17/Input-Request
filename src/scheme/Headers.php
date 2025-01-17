<?php

	namespace App\Headers\Scheme;

	class Headers
	{
		private array $headers;

		function __construct(array $headers) {
			$this->headers = $headers;
		}

		public function header(string $key): mixed {
			return $this->headers[$key] ?? null;
		}

		public function all(): array {
			return $this->headers;
		}
		
		public function method(): string {
			return $_SERVER['REQUEST_METHOD'] ?? '';
		}
	}