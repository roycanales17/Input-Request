<?php
	
	namespace App\Http\Requests;

	class Request extends Blueprint
	{	
		function __construct()
		{
			$this->setInputPayload();
		}
		
		public function inputs(): array {
			return $this->getInput();
		}
		
		public function input( string $name ): mixed {
			return $this->getInput( $name );
		}

        public function query( string $name ): mixed
        {
            $get = $this->inputPayload( 'GET' );
            return $get[ strtolower( $name ) ] ?? '';
        }

        public function post( string $name ): mixed
        {
            $post = $this->inputPayload( 'POST' );
            return $post[ strtolower( $name ) ] ?? '';
        }

        public function json( string $name ): mixed
        {
            $json = $this->inputPayload( 'JSON' );
            return $json[ strtolower( $name ) ] ?? '';
        }

        public function file( string $name ): mixed
        {
            $file = $this->inputPayload( 'FILES' );
            return $file[ strtolower( $name ) ] ?? '';
        }
		
		public function has( string $key ): bool {
			return array_key_exists( strtolower( $key ), $this->inputs() );
		}
		
		public function method(): string {
			return $this->getMethod();
		}
		
		public function only( array $input_keys ): array
		{
			$stored = [];
			$inputs = $this->inputs();
			foreach ( array_map( 'strtolower', $input_keys ) as $key ) {
				if ( isset( $inputs[ $key ] ) ) {
					$stored[ $key ] = $inputs[ $key ];
				}
			}
			
			return $stored;
		}
		
		public function except( array $input_keys ): array
		{
			$array = [];
			foreach ( $this->inputs() as $key => $value )
			{
				if ( !in_array( $key, array_map( 'strtolower', $input_keys ) ) ) {
					$array[ $key ] = $value;
				}
			}
			
			return $array;
		}
		
		public function errors( bool $force_all = false ): array
		{
			if ( $force_all )
				return $this->getResponse();
			
			$msg = [];
			foreach ( $this->getResponse() as $res_key => $res_value ) {
				if ( !preg_match('/\[[^\]]*\]/', $res_key ) )
					$msg[ $res_key ] = $res_value;
			}
			return $msg;
		}
		
		public function error( string $key ): mixed
		{
			$res = $this->errors( true );
			return $res[ $key ] ?? "";
		}
		
		public function isMatched( string $key, mixed $value ): bool {
			return $this->input( $key ) === $value;
		}

		public function isSuccess(): bool
		{
			$this->startValidate();
			return count( $this->getResponse() ) === 0;
		}
		
		public function isFailed(): bool
		{
			$this->startValidate();
			return count( $this->getResponse() ) > 0;
		}
		
		public function validate( array $array ): self
		{
			$this->resetProperty();
			$this->setValidateProperty( $array );
			return $this;
		}
		
		public function message( array $array ): void {
			$this->setMessageProperty( $array );
		}

        public function response( int $code = 200 ): Response
        {
            return new Response( $code );
        }

        public function params( string $name = '' ): array|string {
            if ( $name ) {
                return self::$params[ $name ] ?? '';
            }
            return self::$params;
        }
	}