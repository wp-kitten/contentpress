<?php

namespace App\Helpers;

/**
 * Class UserNotices
 * @package App\Helpers
 *
 * Standard Singleton
 */
class UserNotices
{
    /**
     * The name of the session variable that will be used to store the flash messages
     */
    const SESSION_NAME = 'vp_user_notices';

    /**
     * @var array
     */
    private $notices = [];

    /**
     * @var UserNotices|null
     */
    private static $_instance = null;

    private static $_allowedTypes = [
        'info',
        'success',
        'warning',
        'danger',
    ];

    /**
     * UserNotices constructor.
     */
    private function __construct()
    {

    }

    public static function getInstance()
    {
        if ( !self::$_instance ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function addNotice( $type, $message = '' )
    {
        if ( $this->__isValidType( $type ) && !empty( $message ) ) {
            array_push( $this->notices, [
                'type' => $type,
                'content' => $message,
            ] );
            session()->flash( self::SESSION_NAME, $this->notices );
        }
    }

    public function getAll()
    {
        return session()->get( self::SESSION_NAME );
    }

    public function removeAll()
    {
        $this->notices = [];
        session()->remove( self::SESSION_NAME );
        return $this;
    }

    private function __isValidType( $type = '' ): bool
    {
        return ( $type && in_array( $type, self::$_allowedTypes ) );
    }
}
