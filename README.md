# REQUEST CLASS

Install the bundle using Composer:

```
composer require roy404/request
```

# DOCUMENTATION

This PHP class is designed to handle HTTP requests and provide validation and error handling functionalities. It extends the `Blueprints` class and provides methods for validating inputs, accessing request data, and checking for errors.

## Usage

1. **Instantiate the class:** Create an instance of the `Request` class to access its methods.

   ```php
   $request = new Request();

   # Access request data: Use the input(), json(), query(), file(), post(), inputs(), has(), method(), only(), and except() methods to access request data.
   $email = $request->input('email');
   $inputs = $request->inputs();
   $hasEmail = $request->has('email');
   $method = $request->method();
   $filteredInputs = $request->only(['email', 'name']);
   $filteredInputs = $request->except(['password']);
   ```

2. **Validate inputs:** Use the validate() method to specify validation rules and the isSuccess() and isFailed() methods to check if validation was successful.

    ```php
   $request->validate([
        'email' => 'required|email',
        'file' => 'required|file|extensions:xlsx,pdf|max:200',
        'images' => 'required|image|dimensions:max_width=1800,max_height=1500'
    ]);

    if ($request->isSuccess()) {
        // Validation successful
    } else {
        // Validation failed
        $errors = $request->errors();
    }
    ```

3.  **Set custom error messages:** Use the message() method before the isSuccess() and isFailed() to set custom error messages for validation rules.

    ```php
    $request->message([
        'email' => [
            'required' =>  'Email address is required.'
        ],
        'file' => [
            'required' => 'Please upload a file.'
        ]
    ]);
    ```

## Available Methods

- `inputs(): array`: Get all input values.
- `input(string $name): mixed`: Get the value of a specific input.
- `has(string $key): bool`: Check if an input exists.
- `method(): string`: Get the HTTP request method.
- `only(array $input_keys): array`: Get only the specified input values.
- `except(array $input_keys): array`: Get all input values except the specified keys.
- `errors(bool $force_all = false): array`: Get validation errors.
- `error(string $key): mixed`: Get the error message for a specific input key.
- `isMatched(string $key, mixed $value): bool`: Check if the input value matches the specified value.
- `isSuccess(): bool`: Check if validation was successful.
- `isFailed(): bool`: Check if validation failed.
- `validate(array $array): self`: Set validation rules.
- `message(array $array): void`: Set custom error messages.

## Validation Rules
- `image`: Ensures that the input is a valid image file, such as JPEG, PNG, GIF, SVG, or WEBP.
- `file`: Validates that the input is a valid file upload.
- `required`: Checks that the input is not empty.
- `array`: Verifies that the input is an array.
- `null`: Checks that the input is null.
- `numeric`: Ensures that the input is a numeric value.
- `integer`: Checks that the input is an integer.
- `string`: Verifies that the input is a string.
- `email`: Validates that the input is a valid email address.
- `password`: Validates that the input is a valid password.
- `confirmed`: Checks that the input value matches another field (typically used for password confirmation).
- `mimes`: Specifies the allowed MIME types for file uploads, such as JPEG, PNG, PDF, etc.
- `extensions`: Specifies the allowed file extensions for file uploads, such as pdf, xlsx, etc.
- `max`: Specifies the maximum file size in MB allowed for file uploads or the maximum characters length for strings.
- `min`: Specifies the minimum characters length for strings.
- `dimensions`: Validates the dimensions (width and height) of an image file, enforcing minimum or maximum width and height requirements for images. It supports options like `max_width`, `max_height`, `min_width`, `min_height`, `width`, and `height`.
