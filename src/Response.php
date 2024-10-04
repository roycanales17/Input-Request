<?php

    namespace App\Http\Requests;

    class Response {

        protected int $code;
        protected string $charset;

        function __construct( int $code = 200, string $charset = 'UTF-8' ) {
            header( "HTTP/1.1 $code {$this->getStatusMessage( $code )}" );
            $this->charset = $charset;
        }

        function json( mixed $data = '' ): string|false {
            header( "Content-Type: application/json; charset={$this->charset}", true, $this->code );
            $jsonResponse = json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

            if ( $jsonResponse === false ) {
                $error[ 'error' ] = 'Failed to encode data to JSON';
                $error[ 'details' ] = json_last_error_msg();
                http_response_code( 500 );
                return json_encode( $error, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
            }
            return $jsonResponse;
        }

        function html( mixed $data = '' ): string {
            header( "Content-Type: text/html; charset={$this->charset}", true, $this->code );

            if ( is_array( $data ) || is_object( $data ) ) {
                return '<pre>' . htmlspecialchars( json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ), ENT_QUOTES, 'UTF-8' ) . '</pre>';
            }
            return htmlspecialchars( (string) $data, ENT_QUOTES, 'UTF-8' );
        }

        protected function getStatusMessage( int $code ): string {
            $statusMessages = [
                200 => 'OK',
                400 => 'Bad Request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not Found',
                500 => 'Internal Server Error'
            ];
            return $statusMessages[ $code ] ?? 'Unknown Status Code';
        }
    }