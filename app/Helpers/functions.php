<?php
/**
 * helper method for the var_export function
 * @param null $data
 */
function vd( $data = null )
{
    echo '<div><pre>' . var_export( $data, 1 ) . '</pre></div>';
}
