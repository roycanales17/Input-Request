<?php

	namespace App\Headers\Scheme\Validation;

	class Files
	{
		private array $files;
		private array $allowedMimeTypes = [
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png' => 'image/png',
			'gif' => 'image/gif',
			'webp' => 'image/webp',
			'pdf' => 'application/pdf',
			'txt' => 'text/plain',
			'doc' => 'application/msword',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'ppt' => 'application/vnd.ms-powerpoint',
			'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'xls' => 'application/vnd.ms-excel',
			'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'csv' => 'text/csv',
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'7z' => 'application/x-7z-compressed',
			'mp3' => 'audio/mpeg',
			'wav' => 'audio/wav',
			'ogg' => 'audio/ogg',
			'mp4' => 'video/mp4',
			'webm' => 'video/webm',
			'mov' => 'video/quicktime',
			'avi' => 'video/x-msvideo',
			'mkv' => 'video/x-matroska',
			'html' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'svg' => 'image/svg+xml',
			'ico' => 'image/x-icon',
			// Add more extensions and MIME types as needed
		];

		public static function validate(array $files): self {
			return new self($files);
		}

		function __construct(array $files) {
			$this->files = $files;
		}

		public function error(): bool {
			foreach ($this->files as $attr) {
				if ($attr['error'] !== UPLOAD_ERR_OK)
					return false;
			}
			return true;
		}

		public function required(): bool {
			return !empty($this->files);
		}

		public function file(): bool {
			foreach ($this->files as $attr) {
				if (!is_file($attr['tmp_name'])) {
					return false;
				}
			}
			return true;
		}

		public function mimes(string|array $allowedExtensions): bool {
			$extensions = explode(',', $allowedExtensions);
			$allowedMimes = array_intersect_key($this->allowedMimeTypes, array_flip($extensions));

			foreach ($this->files as $attr) {
				if (!in_array($attr['type'], $allowedMimes)) {
					return false;
				}
			}
			return true;
		}

		public function max(int $maxSize): bool {
			foreach ($this->files as $attr) {
				if ($attr['size'] > $maxSize) { // Size in bytes
					return false;
				}
			}
			return true;
		}

		public function min(int $minSize): bool {
			foreach ($this->files as $attr) {
				if ($attr['size'] < $minSize) { // Size in bytes
					return false;
				}
			}
			return true;
		}

		public function image(): bool {
			return $this->mimes('jpg,jpeg,png,webp');
		}

		public function dimensions($dimensions): bool {

			$dimensionRules = [
				'min_width' => 0,
				'min_height' => 0,
				'max_width' => PHP_INT_MAX,
				'max_height' => PHP_INT_MAX,
				'width' => null,
				'height' => null,
			];

			foreach (explode(',', $dimensions) as $rule) {
				$ruleParts = explode('=', $rule);

				if (count($ruleParts) === 2) {
					[$key, $value] = $ruleParts;

					if (array_key_exists($key, $dimensionRules)) {
						$dimensionRules[$key] = is_numeric($value) ? (int)$value : $value;
					}
				}
			}

			$min_width = $dimensionRules['min_width'];
			$min_height = $dimensionRules['min_height'];
			$max_width = $dimensionRules['max_width'];
			$max_height = $dimensionRules['max_height'];
			$required_width = $dimensionRules['width'];
			$required_height = $dimensionRules['height'];

			foreach ($this->files as $attr) {
				$imageInfo = getimagesize($attr['tmp_name']);
				if ($imageInfo === false) {
					return false;
				}

				[$width, $height] = $imageInfo;

				if (
					$width < $min_width || $height < $min_height ||
					$width > $max_width || $height > $max_height
				) {
					return false;
				}

				if ($required_width !== null && $width !== $required_width) {
					return false;
				}

				if ($required_height !== null && $height !== $required_height) {
					return false;
				}
			}

			return true;
		}

		public function size(int $exactSize): bool {
			foreach ($this->files as $attr) {
				if ($attr['size'] !== $exactSize) { // Size in bytes
					return false;
				}
			}
			return true;
		}
	}