<?php

	namespace App\Headers\Scheme\Validation;

	use App\Headers\Request;

	class Messages
	{
		private string $key;
		private bool $isFile;

		public static function fetch(string $key): self {
			return new self($key);
		}

		function __construct(string $key) {
			$this->key = ucfirst($key);
			$this->isFile = (new Request)->isFile($key);
		}

		public function required(): string {
			if ($this->isFile) {
				return "{$this->key} file is required.";
			}

			return "{$this->key} is required.";
		}

		public function email(): string {
			return "Invalid email address.";
		}

		public function nullable(): string {
			return "{$this->key} must be null.";
		}

		public function string(): string {
			return "{$this->key} must be a string.";
		}

		public function alpha(): string {
			return "{$this->key} must contain only alphabetic characters.";
		}

		public function alpha_dash(): string {
			return "{$this->key} must contain only alphanumeric characters, dashes, or underscores.";
		}

		public function alpha_num(): string {
			return "{$this->key} must contain only alphanumeric characters.";
		}

		public function integer(): string {
			return "{$this->key} must be a valid number.";
		}

		public function int(): string {
			return $this->integer();
		}

		public function bool(): string {
			return "{$this->key} must be a valid boolean.";
		}

		public function array(): string {
			return "{$this->key} must be an array.";
		}

		public function max($max): string {
			if ($this->isFile) {
				$maxKilobytes = $max / 1024;
				return "{$this->key} file must not exceed {$maxKilobytes} KB.";
			}

			return "{$this->key} exceeds the maximum allowed length.";
		}

		public function min($min): string {
			if ($this->isFile) {
				$minKilobytes = $min / 1024;
				return "{$this->key} must be at least {$minKilobytes} KB.";
			}

			return "{$this->key} does not meet the minimum required length.";
		}

		public function size($size): string {
			if ($this->isFile) {
				$exactKilobytes = $size / 1024;
				return "{$this->key} must be exactly {$exactKilobytes} KB ({$size} bytes).";
			}

			return "{$this->key} must have an exact size of $size.";
		}

		public function in($allowedValues): string {
			return "{$this->key} is not in the allowed list of values.";
		}

		public function not_in($value): string {
			return "{$this->key} is in the disallowed list of values.";
		}

		public function image(): string {
			return "{$this->key} must be a valid image file.";
		}

		public function mimes($value): string {
			return "{$this->key} must be a valid file of type: $value.";
		}

		public function dimensions($value): string {
			return "{$this->key} file must meet the required dimensions: {$value}. Please ensure the width and height are within the allowed range.";
		}

		public function numeric($value): string {
			return "{$this->key} must be a valid number.";
		}
	}