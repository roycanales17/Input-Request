<?php

	namespace App\Headers\Requests;

	use App\Headers\Scheme\Validation\Files;
	use App\Headers\Scheme\Validation\Messages;
	use App\Headers\Request;
	use App\Headers\Scheme\Validation\Inputs;

	trait Validate
	{
		private Request $request;
		private array $validate;
		private bool $isSuccess;
		private array $messages = [];
		private array $response = [];
		private bool $validated = false;

		protected function validate(): bool
		{
			if ($this->validated)
				return $this->getStatus();

			$request = $this->request;
			foreach ($this->validate as $inputKey => $rules) {

				$result = true;
				$inputValue = $request->input($inputKey);
				$isFile = $request->isFile($inputKey);

				if ($isFile)
					$rules['error'] = 1;

				foreach ($rules as $rule => $ruleValue) {

					if (!$result)
						continue;

					if ($isFile) {

						if (method_exists(Files::class, $rule)) {
							$result = Files::validate($inputValue)->$rule($ruleValue);

							if (!$result) {
								$this->registerResponse($inputKey, $rule, $ruleValue);
							}
						}
					} else {
						if (method_exists(Inputs::class, $rule)) {
							$result = Inputs::validate($inputValue)->$rule($ruleValue);

							if (!$result) {
								$this->registerResponse($inputKey, $rule, $ruleValue);
							}
						}
					}
				}
			}

			$this->validated = true;
			return $result ?? true;
		}

		protected function registerResponse(string $inputKey, string $rule,mixed $ruleValue): void
		{
			if (isset($this->messages[$inputKey][$rule])) {
				$this->response[$inputKey] = $this->messages[$inputKey][$rule];
				return;
			}

			$this->response[$inputKey] = Messages::fetch($inputKey)->$rule($ruleValue);
		}

		protected function registerMessages(array $messages): void
		{
			$this->messages = $messages;
		}

		protected function registerValidate(array $validate): void
		{
			foreach ($validate as $key => $value) {
				$rules = [];
				$fields = explode('|', $value);
				foreach ($fields as $field) {
					if (str_contains($field, ':')) {
						$temp_r = explode(':', $field);
						$rules[$temp_r[0]] = $temp_r[1];
					} else {
						$rules[$field] = true;
					}
				}
				$this->validate[$key] = $rules;
			}
		}

		protected function registerRequests(Request $req): void
		{
			$this->request = $req;
		}

		protected function toggleStatus(bool $status): void
		{
			$this->isSuccess = $status;
		}

		protected function getStatus(): bool
		{
			return $this->isSuccess;
		}

		protected function getResponse(): array
		{
			return $this->response;
		}
	}