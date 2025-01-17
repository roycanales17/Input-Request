<?php

	namespace App\Headers;

	use JetBrains\PhpStorm\NoReturn;

	class Response
	{
		private mixed $content;
		private int $statusCode;
		private array $headers;

		function __construct(mixed $content = '', int $statusCode = 200, array $headers = [])
		{
			$this->content = $content;
			$this->statusCode = $statusCode;
			$this->headers = $headers;

			http_response_code($this->statusCode);

			foreach ($this->headers as $name => $value) {
				$this->header($name, $value);
			}
		}

		public function header(string $key, mixed $value): self
		{
			header("$key: $value");
			return $this;
		}

		public function send(): void
		{
			if ($this->content) {
				if (is_array($this->content)) {
					echo json_encode($this->content);
				} else {
					echo $this->content;
				}
			}
		}

		public function download(string $filename, mixed $content = null): void
		{
			if ($content === null) {
				throw new \Exception('Content must be provided for the download.');
			}

			$this->header('Content-Type', 'application/octet-stream');
			$this->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
			$this->header('Content-Length', strlen($content));

			echo $content;
		}

		#[NoReturn] public function redirect(string $url, int $statusCode = 302): void
		{
			header("Location: $url", true, $statusCode);
			exit();
		}

		public function file(string $filename): void
		{
			if (file_exists($filename)) {
				$this->header('Content-Type', mime_content_type($filename));
				$this->header('Content-Disposition', 'inline; filename="' . basename($filename) . '"');
				$this->header('Content-Length', filesize($filename));

				readfile($filename);
			} else {
				http_response_code(404);
				echo 'File Not Found';
			}
		}
	}
