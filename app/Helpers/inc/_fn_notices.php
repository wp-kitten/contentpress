<?php

use App\Helpers\UserNotices;

/**
 * Helper method to add a user notice
 * @param string $type The notification type (all supported alert classes by Bootstrap 4)
 * @param string $notice
 * @see https://getbootstrap.com/docs/4.0/components/alerts/
 */
function vp_add_user_notice( string $type, string $notice )
{
    UserNotices::getInstance()->addNotice( $type, $notice );
}

/**
 * Helper method to remove all user notices
 */
function vp_remove_all_user_notices(){
    UserNotices::getInstance()->removeAll();
}
