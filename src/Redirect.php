<?php

	namespace App\Headers;

	class Redirect
	{
		private string $url;
		private int $code;

		public function __construct(string $url, int $code = 302)
		{
			$this->url = $url;
			$this->code = $code;
		}

		/**
		 * Flash data to the session for the next request.
		 */
		public function with(string|array $key, mixed $value = null): bool
		{
			if (class_exists('App\\Utilities\\Session') && method_exists('App\\Utilities\\Session', 'flash')) {
				if (is_array($key)) {
					foreach ($key as $k => $v) {
						\App\Utilities\Session::flash($k, $v);
					}
				} else {
					\App\Utilities\Session::flash($key, $value);
				}
			}

			$this->send();
			return true;
		}

		/**
		 * Flash validation or system errors.
		 */
		public function withErrors(string|array $errors): bool
		{
			if (class_exists('App\\Utilities\\Session') && method_exists('App\\Utilities\\Session', 'flash')) {
				\App\Utilities\Session::flash('errors', (array) $errors);
			}

			$this->send();
			return true;
		}

		/**
		 * Flash success messages.
		 */
		public function withSuccess(string $message): bool
		{
			if (class_exists('App\\Utilities\\Session') && method_exists('App\\Utilities\\Session', 'flash')) {
				\App\Utilities\Session::flash('success', $message);
			}

			$this->send();
			return true;
		}

		/**
		 * Redirect back to the previous page.
		 */
		public static function back(): self
		{
			$referer = '/';
			if (class_exists('App\\Utilities\\Request') && method_exists('App\\Utilities\\Request', 'header')) {
				$referer = \App\Utilities\Request::header('HTTP_REFERER') ?? '/';
			} elseif (!empty($_SERVER['HTTP_REFERER'])) {
				$referer = $_SERVER['HTTP_REFERER'];
			}

			return new self($referer);
		}

		/**
		 * Refresh current page.
		 */
		public static function refresh(): self
		{
			return new self($_SERVER['REQUEST_URI'] ?? '/');
		}

		/**
		 * Internal redirect handler.
		 */
		public function send(): never
		{
			$url = filter_var($this->url, FILTER_SANITIZE_URL);
			$code = $this->code;

			// Detect AJAX request
			$isAjax = false;

			if (class_exists('App\\Utilities\\Server') && method_exists('App\\Utilities\\Server', 'isAjaxRequest')) {
				$isAjax = \App\Utilities\Server::isAjaxRequest();
			} elseif (class_exists('App\\Utilities\\Request') && method_exists('App\\Utilities\\Request', 'header')) {
				$isAjax = strtolower(\App\Utilities\Request::header('HTTP_X_REQUESTED_WITH') ?? '') === 'xmlhttprequest';
			} elseif (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
				$isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
			}

			if ($isAjax) {
				$flashData = [];

				if (class_exists('App\\Utilities\\Session') && method_exists('App\\Utilities\\Session', 'pullFlashedData')) {
					$flashData = \App\Utilities\Session::pullFlashedData() ?? [];
				}

				exit((new Response)->json([
					'redirect' => $url,
					'flash' => $flashData,
				]));
			}

			// Validate redirect codes
			$validCodes = [301, 302, 303, 307, 308];
			if (!in_array($code, $validCodes, true)) {
				$code = 302;
			}

			header("Location: $url", true, $code);
			exit();
		}
	}
