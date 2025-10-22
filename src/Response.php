<?php

	namespace App\Headers;

	use SimpleXMLElement;

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
			header("$key: $value", true, $this->statusCode);
			return $this;
		}

		public function html(): string
		{
			$this->header('Content-Type', 'text/html; charset=UTF-8');
			return (string) $this->content;
		}

		public function json(mixed $value = ''): string
		{
			$content = $this->content;
			if ($value)
				$content = $value;

			$this->header('Content-Type', 'application/json; charset=UTF-8');
			return json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		}

		public function text(): string
		{
			$this->header('Content-Type', 'text/plain; charset=UTF-8');
			return (string) $this->content;
		}

		public function javascript(): string
		{
			$this->header('Content-Type', 'application/javascript; charset=UTF-8');
			return (string) $this->content;
		}

		public function css(): string
		{
			$this->header('Content-Type', 'text/css; charset=UTF-8');
			return (string) $this->content;
		}

		public function csv(): string
		{
			$this->header('Content-Type', 'text/csv; charset=UTF-8');
			$this->header('Content-Disposition', 'attachment; filename="export.csv"');

			$csv = '';
			if (is_array($this->content)) {
				foreach ($this->content as $row) {
					$csv .= implode(',', $row) . "\n";
				}
			}
			return $csv;
		}

		public function pdf(): string
		{
			$this->header('Content-Type', 'application/pdf');
			$this->header('Content-Disposition', 'attachment; filename="file.pdf"');

			return (string) $this->content;
		}

		public function image(string $type = 'jpeg'): string
		{
			$mimeTypes = [
				'jpeg' => 'image/jpeg',
				'png' => 'image/png',
				'gif' => 'image/gif',
			];

			if (!isset($mimeTypes[$type])) {
				$type = 'jpeg';
			}

			$this->header('Content-Type', $mimeTypes[$type]);
			return (string) $this->content;
		}

		public function audio(string $type = 'mpeg'): string
		{
			$mimeTypes = [
				'mpeg' => 'audio/mpeg',
				'ogg' => 'audio/ogg',
			];

			if (!isset($mimeTypes[$type])) {
				$type = 'mpeg';
			}

			$this->header('Content-Type', $mimeTypes[$type]);
			return (string) $this->content;
		}

		public function multipart(): string
		{
			$this->header('Content-Type', 'multipart/form-data');
			return (string) $this->content;
		}

		public function xml(): string
		{
			$this->header('Content-Type', 'application/xml; charset=UTF-8');

			$xml = new SimpleXMLElement('<root/>');
			$this->arrayToXml($this->content, $xml);

			return $xml->asXML();
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

		public function redirect(string $url): Redirect
		{
			return new Redirect($url);
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

		private function arrayToXml(array $data, SimpleXMLElement $xml): void
		{
			foreach ($data as $key => $value) {
				if (is_array($value)) {
					$subnode = $xml->addChild($key);
					$this->arrayToXml($value, $subnode);
				} else {
					$xml->addChild($key, htmlspecialchars((string) $value));
				}
			}
		}
	}
