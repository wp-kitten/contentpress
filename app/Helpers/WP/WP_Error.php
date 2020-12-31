<?php

namespace App\Helpers\WP;

/**
 * ValPress Error class.
 *
 * Container for checking for WordPress errors and error messages. Return
 * WP_Error and use is_wp_error() to check if this class is returned. Many
 * core WordPress functions pass this class in the event of an error and
 * if not handled properly will result in code errors.
 *
 * @since WordPress 2.1.0
 */
class WP_Error
{
    /**
     * Stores the list of errors.
     *
     * @var array
     */
    public $errors = [];

    /**
     * Stores the list of data for error codes.
     * @var array
     */
    public $error_data = [];

    /**
     * Initialize the error.
     *
     * If `$code` is empty, the other parameters will be ignored.
     * When `$code` is not empty, `$message` will be used even if
     * it is empty. The `$data` parameter will be used only if it
     * is not empty.
     *
     * Though the class is constructed with a single error code and
     * message, multiple codes can be added using the `add()` method.
     *
     * @param string|int $code Error code
     * @param string $message Error message
     * @param mixed $data Optional. Error data.
     *
     */
    public function __construct( $code = '', $message = '', $data = '' )
    {
        if ( empty( $code ) ) {
            return;
        }

        $this->errors[ $code ][] = $message;

        if ( !empty( $data ) ) {
            $this->error_data[ $code ] = $data;
        }
    }

    /**
     * Retrieve all error codes.
     *
     * @return array List of error codes, if available.
     * @since 2.1.0
     *
     */
    public function get_error_codes()
    {
        return ( $this->has_errors() ? array_keys( $this->errors ) : [] );
    }

    /**
     * Retrieve first error code available.
     *
     * @return string|int Empty string, if no error codes.
     * @since 2.1.0
     *
     */
    public function get_error_code()
    {
        $codes = $this->get_error_codes();
        return ( empty( $codes ) ? '' : $codes[ 0 ] );
    }

    /**
     * Retrieve all error messages or error messages matching code.
     *
     * @param string|int $code Optional. Retrieve messages matching code, if exists.
     * @return array Error strings on success, or empty array on failure (if using code parameter).
     * @since 2.1.0
     *
     */
    public function get_error_messages( $code = '' )
    {
        // Return all messages if no code specified.
        if ( empty( $code ) ) {
            $all_messages = [];
            foreach ( (array)$this->errors as $code => $messages ) {
                $all_messages = array_merge( $all_messages, $messages );
            }
            return $all_messages;
        }

        if ( isset( $this->errors[ $code ] ) ) {
            return $this->errors[ $code ];
        }
        return [];
    }

    /**
     * Get single error message.
     *
     * This will get the first message available for the code. If no code is
     * given then the first code available will be used.
     *
     * @param string|int $code Optional. Error code to retrieve message.
     * @return string
     * @since 2.1.0
     *
     */
    public function get_error_message( $code = '' )
    {
        if ( empty( $code ) ) {
            $code = $this->get_error_code();
        }
        $messages = $this->get_error_messages( $code );
        return ( empty( $messages ) ? '' : $messages[ 0 ] );
    }

    /**
     * Retrieve error data for error code.
     *
     * @param string|int $code Optional. Error code.
     * @return mixed Error data, if it exists.
     * @since 2.1.0
     *
     */
    public function get_error_data( $code = '' )
    {
        if ( empty( $code ) ) {
            $code = $this->get_error_code();
        }

        if ( isset( $this->error_data[ $code ] ) ) {
            return $this->error_data[ $code ];
        }
        return false;
    }

    /**
     * Verify if the instance contains errors.
     *
     * @return bool
     * @since 5.1.0
     *
     */
    public function has_errors()
    {
        return ( !empty( $this->errors ) );
    }

    /**
     * Add an error or append additional message to an existing error.
     *
     * @param string|int $code Error code.
     * @param string $message Error message.
     * @param mixed $data Optional. Error data.
     * @since 2.1.0
     *
     */
    public function add( $code, $message, $data = '' )
    {
        $this->errors[ $code ][] = $message;
        if ( !empty( $data ) ) {
            $this->error_data[ $code ] = $data;
        }
    }

    /**
     * Add data for error code.
     *
     * The error code can only contain one error data.
     *
     * @param mixed $data Error data.
     * @param string|int $code Error code.
     * @since 2.1.0
     *
     */
    public function add_data( $data, $code = '' )
    {
        if ( empty( $code ) ) {
            $code = $this->get_error_code();
        }
        $this->error_data[ $code ] = $data;
    }

    /**
     * Removes the specified error.
     *
     * This function removes all error messages associated with the specified
     * error code, along with any error data for that code.
     *
     * @param string|int $code Error code.
     * @since 4.1.0
     *
     */
    public function remove( $code )
    {
        unset( $this->errors[ $code ] );
        unset( $this->error_data[ $code ] );
    }
}
