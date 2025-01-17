<?php

	namespace App\Headers\Scheme;

	use App\Headers\Request;
	use App\Headers\Requests\Validate as ValidateRequest;

	class Validate
	{
		use ValidateRequest;

		function __construct(array $validate) {
			$this->registerValidate($validate);
			$this->registerRequests(new Request);
		}

		public function message(array $customMessages): void
		{
			$this->registerMessages($customMessages);
		}

		public function isSuccess(): bool
		{
			$this->toggleStatus($this->validate());
			return $this->getStatus();
		}

		public function isFailed(): bool
		{
			$this->toggleStatus($this->validate());
			return !$this->isSuccess();
		}

		public function getErrors(): array
		{
			$this->toggleStatus($this->validate());
			return $this->getResponse();
		}
	}