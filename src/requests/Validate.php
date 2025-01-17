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
		private array $response = [];

		protected function validate(): bool
		{
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
								$this->response[$inputKey] = Messages::fetch($inputKey)->$rule($ruleValue);
							}
						}
					} else {
						if (method_exists(Inputs::class, $rule)) {
							$result = Inputs::validate($inputValue)->$rule($ruleValue);

							if (!$result) {
								$this->response[$inputKey] = Messages::fetch($inputKey)->$rule($ruleValue);
							}
						}
					}
				}
			}

			return false;
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