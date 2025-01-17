<?php

	namespace App\Headers\Requests;

	trait Inputs
	{
		private array $inputs = [
			'query',
			'post',
			'json',
			'raw',
			'files'
		];

		private array $initializedInputs = [];

		public function inputs(bool $mixed = true): array
		{
			if (count($this->initializedInputs)) {
				if ($mixed && isset($this->initializedInputs['mixed'])) {
					return $this->initializedInputs['mixed'];
				}

				if (!$mixed && isset($this->initializedInputs['non-mixed'])) {
					return $this->initializedInputs['non-mixed'];
				}
			}

			$inputs = [];
			foreach ($this->inputs as $input) {
				if ($mixed) {
					$inputs += self::fetch($input);
				} else {
					$inputs[$input] = self::fetch($input);
				}
			}

			$this->initializedInputs[$mixed ? 'mixed' : 'non-mixed'] = $inputs;
			return $inputs;
		}

		public function input(string $key): mixed
		{
			$inputs = $this->inputs();
			return $inputs[$key] ?? null;
		}

		public function query(string $key): mixed
		{
			$inputs = $this->inputs(false);
			return $inputs['query'][$key] ?? null;
		}

		public function json(string $key): mixed
		{
			$inputs = $this->inputs(false);
			return $inputs['json'][$key] ?? null;
		}

		public function post(string $key): mixed
		{
			$inputs = $this->inputs(false);
			return $inputs['post'][$key] ?? null;
		}

		public function file(string $key): mixed
		{
			$inputs = $this->inputs(false);
			return $inputs['files'][$key] ?? null;
		}

		public function raw(string $key): mixed
		{
			$inputs = $this->inputs(false);
			return $inputs['raw'][$key] ?? null;
		}

		public function has(string $key): bool
		{
			$inputs = $this->inputs();
			return (bool)($inputs[$key] ?? false);
		}

		public function only(array|string $keysOrKey): array
		{
			$inputs = [];

			if (is_string($keysOrKey))
				$keysOrKey = [$keysOrKey];

			foreach ($keysOrKey as $key) {
				if ($this->has($key)) {
					$inputs[$key] = $this->input($key);
				}
			}

			return $inputs;
		}

		public function except(array|string $keysOrKey): array
		{
			$fields = $this->inputs();

			if (is_string($keysOrKey))
				$keysOrKey = [$keysOrKey];

			foreach ($keysOrKey as $key) {
				if (isset($fields[$key])) {
					unset($fields[$key]);
				}
			}

			return $fields;
		}

		public function isFile(string $key): bool
		{
			$inputs = $this->inputs(false);
			return isset($inputs['files'][$key]);
		}

		public function isMatched(string $key, mixed $valueToMatch): bool
		{
			$input = $this->input($key);
			return !is_array($input) && $input === $valueToMatch;
		}
	}