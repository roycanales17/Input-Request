<?php

	namespace App\Headers\Scheme\Validation;

	class Inputs
	{
		private mixed $value;

		public static function validate(mixed $value): self {
			return new self($value);
		}

		function __construct(mixed $value) {
			$this->value = $value;
		}

		public function required(): bool {
			return trim($this->value ?? '');
		}

		public function email(): bool {
			return $this->value && filter_var($this->value, FILTER_VALIDATE_EMAIL);
		}

		public function nullable(): bool {
			return is_null($this->value);
		}

		public function string(): bool {
			return is_string($this->value);
		}

		public function alpha(): bool {
			return ctype_alpha($this->value);
		}

		public function alpha_dash(): bool {
			return ctype_alnum($this->value);
		}

		public function alpha_num(): bool {
			return ctype_alnum($this->value);
		}

		public function integer(): bool {
			return is_int($this->value);
		}

		public function int(): bool {
			return is_int($this->value);
		}

		public function bool(): bool {
			return is_bool($this->value);
		}

		public function array(): bool {
			return is_array($this->value);
		}

		public function max($max): bool {
			$max = (int)$max;
			return strlen($this->value) <= $max;
		}

		public function min($min): bool {
			$min = (int)$min;
			return strlen($this->value) >= $min;
		}

		public function size($size): bool {
			$size = (int)$size;
			return count($this->value) === $size;
		}

		public function in($allowedValues): bool {
			return is_array($this->value) && in_array($allowedValues, $this->value, true);
		}

		public function not_in($value): bool {
			return !$this->in($value);
		}

		public function numeric(): bool {
			return is_numeric($this->value);
		}
	}