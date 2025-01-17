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
			$this->toggleStatus($this->validate());
		}

		public function isSuccess(): bool
		{
			return $this->getStatus();
		}

		public function isFailed(): bool
		{
			return !$this->isSuccess();
		}

		public function getErrors(): array
		{
			return $this->getResponse();
		}
	}