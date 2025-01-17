<?php

	use App\Headers\Request;

	spl_autoload_register(function ($class) {
		$namespaces = [
			'App\\Headers\\' => __DIR__ . '/src/'
		];
		foreach ($namespaces as $namespace => $baseDir) {
			if (str_starts_with($class, $namespace)) {
				$relativeClass = str_replace($namespace, '', $class);
				$file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

				if (file_exists($file)) {
					require_once $file;
				}
			}
		}
	});

	Request::capture();
	$req = new Request();

	if ($req->isMatched('submit', 'submit')) {
		echo '<pre>';
		$response = $req->validate([
			'email' => 'required|email|max:35',
			'password' => 'required',
			'tests' => 'required|dimensions:min_width=2000,min_height=200'
		]);
		print_r($response->getErrors());
		echo '</pre>';
	}
?>

<form method="post" enctype="multipart/form-data">
	<input type="text" name="email" placeholder="Email Address" />
	<input type="password" name="password" placeholder="Your Password" />
	<input type="file" name="tests[]" multiple>
	<input type="submit" name="submit" value="submit">
</form>

