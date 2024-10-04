<?php
	
	namespace App\Http\Requests;
	
	class Blueprint
	{
		use FileValidation;
		use InputValidation;

        # Costume params
        protected static array $params = [];

		# Inputs is used to store all the input fields submitted.
		protected array $payload = [];
		
		# All the errors response is stored here.
		protected array $response = [];
		
		# Rules applied for validation.
		protected array $validate = [];
		
		# Custom error message when failed.
		protected array $message = [];
		
		# Different types of input request.
		protected array $request = [];
		
		# Required rules to have values.
		protected array $required_rules_values = [ 'max', 'min', 'extension', 'mimes', 'dimensions' ];
		
		# Prohibited rules from the $_FILES input.
		protected array $excluded_files_rules = [ 'null', 'numeric', 'integer', 'string', 'email', 'password', 'confirmed' ];
		
		# Prohibited rules from the $_GET & $_POST input.
		protected array $excluded_non_files_rules = [ 'max', 'mimes', 'extension', 'image', 'dimensions' ];
		
		# Valid images extension.
		protected array $valid_images_ext = [ 'jpeg', 'png', 'gif', 'svg', 'webp' ];
		
		protected function startValidate(): void
		{
			if ( !$this->validate )
			{
				$this->response = [];
				return;
			}
			
			foreach ( $this->validate as $key => $rules )
			{
				$policies = explode( '|', $rules );
				for ( $i = 0; $i < count( $policies ); $i++ )
				{
					$rule = explode( ':', $policies[ $i ] );
					$rule_key = $rule[ 0 ];
					$rule_val = $rule[ 1 ] ?? 0;
					$value = $this->getInput( $key );
					
					if ( !$this->validateRules( $key, $rule_key, $rule_val ) )
						continue;
					
					if ( $rule_val )
					{
						switch ( $rule_key )
						{
							case 'max':
								
								if ( $this->validateFile( $key ) )
									$this->validateMaxFile( $key, intval( $rule_val ) );
								
								elseif ( !$this->validateMax( $value, intval( $rule_val ), $input_key ) )
									$this->setMessage( $key, $this->getMessage( $key, $rule_key, $input_key ) ?: "Must not exceed the maximum ($rule_val) character/s.", $input_key );
								
								break;
							
							case 'min':
								if ( !$this->validateMin( $value, intval( $rule_val ), $input_key ) )
									$this->setMessage( $key, $this->getMessage( $key, $rule_key, $input_key ) ?: "Must not below ($rule_val) character/s.", $input_key );
								break;
							
							case 'dimensions':
								$this->validateFileDimensions( $key, $rule_val );
								break;
							
							case 'extensions':
							case 'mimes':
								$this->validateFileMimeType( $key, $rule_val );
								break;
						}
					}
					else
					{
						switch ( $rule_key )
						{
							case 'required':

								if ( $this->validateFile( $key ) )
								{
									if ( !$this->validateRequiredFile( $key ) )
										$this->setMessage( $key , $this->getMessage( $key, $rule_key ) ?: "Please upload a file." );
								}

								elseif ( !$this->validateRequired( $value, $input_key ) )
									$this->setMessage( $key, $this->getMessage( $key, $rule_key, $input_key ) ?: ucfirst( $key )." is required.", $input_key );
								
								break;

							case 'image':
								if ( !$this->validateImageFile( $key ) )
									$this->setMessage( $key , $this->getMessage( $key, $rule_key ) ?: "Invalid image file." );
								break;
							
							case 'file':
								if ( !$this->validateFile( $key ) )
									$this->setMessage( $key , $this->getMessage( $key, $rule_key ) ?: "Invalid file given (".ucfirst( $key ).")." );
								break;
							
							case 'array':
								if ( !$this->validateArray( $value, $input_key ) )
									$this->setMessage( $key, $this->getMessage( $key, $rule_key, $input_key ) ?: "Invalid array value.", $input_key );
								break;
							
							case 'null':
								if ( !$this->validateNull( $value, $input_key ) )
									$this->setMessage( $key, $this->getMessage( $key, $rule_key, $input_key ) ?: "Invalid null value.", $input_key );
								break;
							
							case 'numeric':
								if ( !$this->validateNumeric( $value, $input_key ) )
									$this->setMessage( $key, $this->getMessage( $key, $rule_key, $input_key ) ?: "Invalid numeric value.", $input_key );
								break;
							
							case 'integer':
								if ( !$this->validateInteger( $value, $input_key ) )
									$this->setMessage( $key, $this->getMessage( $key, $rule_key, $input_key ) ?: "Invalid integer value.", $input_key );
								break;
							
							case 'string':
								if ( !$this->validateString( $value, $input_key ) )
									$this->setMessage( $key, $this->getMessage( $key, $rule_key, $input_key ) ?: "Invalid string value.", $input_key );
								break;
							
							case 'email':
								if ( !$this->validateEmail( $value, $input_key ) )
									$this->setMessage( $key, $this->getMessage( $key, $rule_key, $input_key ) ?: "Invalid email address.", $input_key );
								break;
							
							case 'password':
								if ( !$this->validatePassword( $value, $response ) )
									$this->setMessage( $key, $this->getMessage( $key, $rule_key ) ?: $response );
								break;
							
							case 'confirmed':

								if ( !$this->validateRequired( $this->getInput( 'confirm_password' ) ) )
									$this->setMessage( 'confirm_password', $this->getMessage( $key, $rule_key ) ?: "Password confirmation is required.");

								elseif ( $value !== $this->getInput( 'confirm_password' ) )
									$this->setMessage( 'confirm_password', $this->getMessage( $key, $rule_key ) ?: "Password and confirmation password should be match.");

								break;
						}
					}
				}
			}
		}
		
		protected function getResponse(): array {
			return $this->response;
		}
		
		protected function getMessage( string $input, string $rule, int|string|null $key = null ): string|bool
		{
			$input = strtolower( $input );
			$rule = strtolower( $rule );
			$message = $this->message;
			
			if ( isset( $message[ $input ] ) && isset( $message[ $input ][ $rule ] ) && $msg = $message[ $input ][ $rule ] )
			{
				if ( is_array( $msg ) ) {
					if ( !is_null( $key ) ) {
						if ( isset( $msg[ $key ] ) && $msg2 = $msg[ $key ] )
							return $msg2;
					}
				}
				else return $msg;
			}
			
			return false;
		}
		
		protected function getInput( string $name = '' ): mixed
		{
			if ( !$name )
				return $this->payload;
			
			return $this->payload[ strtolower( $name ) ] ?? "";
		}

        protected function inputPayload( string $type ): array {
            return $this->payload[ strtoupper( $type ) ] ?? [];
        }

		protected function getMethod(): string {
			return $_SERVER[ 'REQUEST_METHOD' ];
		}
		
		protected function setInputPayload(): void
		{
            $jsonData = ( strpos( $_SERVER[ 'CONTENT_TYPE' ] ?? '', 'application/json' ) !== false ) ? file_get_contents( 'php://input' ) : '';
            $this->payload[ "GET" ] = array_change_key_case( $_GET );
            $this->payload[ "POST" ] = array_change_key_case( $_POST );
            $this->payload[ "FILES" ] = array_change_key_case( $_FILES );
            $this->payload[ "JSON" ] = array_change_key_case( json_decode( $jsonData, true ) ?? [] );

            $this->payload = [
                ...$this->payload[ "GET" ],
                ...$this->payload[ "POST" ],
                ...$this->payload[ "FILES" ],
                ...$this->payload[ "JSON" ]
            ];
		}

		protected function setMessage( string $key, string $message = '', string|int|null $input_key = null ): void
		{
			if ( !array_key_exists( $key, $this->response ) )
					$this->response[ $key ] = $message;
				
			if ( !is_null( $input_key ) )
				$this->response[ $key."[$input_key]" ] = $message;
		}

		protected function resetProperty(): void {
			$this->validate = [];
			$this->message = [];
		}

		protected function setValidateProperty( array $array ): void {
			$this->validate = $array;
		}

		protected function setMessageProperty( array $array ): void {
			$this->message = $array;
		}

        public function setParams( array $params ): void {
            self::$params = $params;
        }

        public function include( string $directory ): void {
            if ( !is_dir( $directory ) )
                return;

            $files = scandir( $directory) ;
            $files = array_diff( $files, [ '.', '..' ]);
            foreach ( $files as $file ) {
                $filePath = $directory . DIRECTORY_SEPARATOR . $file;

                if ( is_file( $filePath ) && pathinfo( $filePath, PATHINFO_EXTENSION ) === 'php' ) {
                    require_once $filePath;
                }
            }
        }
	}