<?php


/**
 * Core class used to implement action and filter hook functionality.
 *
 * @since 4.7.0
 *
 * @see Iterator
 * @see ArrayAccess
 */
final class WP_Hook implements \Iterator, \ArrayAccess
{

    /**
     * Hook callbacks.
     *
     * @since 4.7.0
     * @var array
     */
    public $callbacks = [];

    /**
     * The priority keys of actively running iterations of a hook.
     *
     * @since 4.7.0
     * @var array
     */
    private $iterations = [];

    /**
     * The current priority of actively running iterations of a hook.
     *
     * @since 4.7.0
     * @var array
     */
    private $current_priority = [];

    /**
     * Number of levels this hook can be recursively called.
     *
     * @since 4.7.0
     * @var int
     */
    private $nesting_level = 0;

    /**
     * Flag for if we're current doing an action, rather than a filter.
     *
     * @since 4.7.0
     * @var bool
     */
    private $doing_action = false;

    /**
     * Hooks a function or method to a specific filter action.
     *
     * @param string $tag The name of the filter to hook the $function_to_add callback to.
     * @param callable $function_to_add The callback to be run when the filter is applied.
     * @param int $priority The order in which the functions associated with a
     *                                  particular action are executed. Lower numbers correspond with
     *                                  earlier execution, and functions with the same priority are executed
     *                                  in the order in which they were added to the action.
     * @param int $accepted_args The number of arguments the function accepts.
     * @since 4.7.0
     *
     */
    public function add_filter( $tag, $function_to_add, $priority, $accepted_args )
    {
        $idx = _wp_filter_build_unique_id( $tag, $function_to_add, $priority );
        $priority_existed = isset( $this->callbacks[ $priority ] );

        $this->callbacks[ $priority ][ $idx ] = [
            'function' => $function_to_add,
            'accepted_args' => $accepted_args,
        ];

        // if we're adding a new priority to the list, put them back in sorted order
        if ( !$priority_existed && count( $this->callbacks ) > 1 ) {
            ksort( $this->callbacks, SORT_NUMERIC );
        }

        if ( $this->nesting_level > 0 ) {
            $this->resort_active_iterations( $priority, $priority_existed );
        }
    }

    /**
     * Handles reseting callback priority keys mid-iteration.
     *
     * @param bool|int $new_priority Optional. The priority of the new filter being added. Default false,
     *                                   for no priority being added.
     * @param bool $priority_existed Optional. Flag for whether the priority already existed before the new
     *                                   filter was added. Default false.
     * @since 4.7.0
     *
     */
    private function resort_active_iterations( $new_priority = false, $priority_existed = false )
    {
        $new_priorities = array_keys( $this->callbacks );

        // If there are no remaining hooks, clear out all running iterations.
        if ( !$new_priorities ) {
            foreach ( $this->iterations as $index => $iteration ) {
                $this->iterations[ $index ] = $new_priorities;
            }
            return;
        }

        $min = min( $new_priorities );
        foreach ( $this->iterations as $index => &$iteration ) {
            $current = current( $iteration );
            // If we're already at the end of this iteration, just leave the array pointer where it is.
            if ( false === $current ) {
                continue;
            }

            $iteration = $new_priorities;

            if ( $current < $min ) {
                array_unshift( $iteration, $current );
                continue;
            }

            while ( current( $iteration ) < $current ) {
                if ( false === next( $iteration ) ) {
                    break;
                }
            }

            // If we have a new priority that didn't exist, but ::apply_filters() or ::do_action() thinks it's the current priority...
            if ( $new_priority === $this->current_priority[ $index ] && !$priority_existed ) {
                /*
                 * ... and the new priority is the same as what $this->iterations thinks is the previous
                 * priority, we need to move back to it.
                 */

                if ( false === current( $iteration ) ) {
                    // If we've already moved off the end of the array, go back to the last element.
                    $prev = end( $iteration );
                }
                else {
                    // Otherwise, just go back to the previous element.
                    $prev = prev( $iteration );
                }
                if ( false === $prev ) {
                    // Start of the array. Reset, and go about our day.
                    reset( $iteration );
                }
                elseif ( $new_priority !== $prev ) {
                    // Previous wasn't the same. Move forward again.
                    next( $iteration );
                }
            }
        }
        unset( $iteration );
    }

    /**
     * Unhooks a function or method from a specific filter action.
     *
     * @param string $tag The filter hook to which the function to be removed is hooked. Used
     *                                     for building the callback ID when SPL is not available.
     * @param callable $function_to_remove The callback to be removed from running when the filter is applied.
     * @param int $priority The exact priority used when adding the original filter callback.
     * @return bool Whether the callback existed before it was removed.
     * @since 4.7.0
     *
     */
    public function remove_filter( $tag, $function_to_remove, $priority )
    {
        $function_key = _wp_filter_build_unique_id( $tag, $function_to_remove, $priority );

        $exists = isset( $this->callbacks[ $priority ][ $function_key ] );
        if ( $exists ) {
            unset( $this->callbacks[ $priority ][ $function_key ] );
            if ( !$this->callbacks[ $priority ] ) {
                unset( $this->callbacks[ $priority ] );
                if ( $this->nesting_level > 0 ) {
                    $this->resort_active_iterations();
                }
            }
        }
        return $exists;
    }

    /**
     * Checks if a specific action has been registered for this hook.
     *
     * @param string $tag Optional. The name of the filter hook. Used for building
     *                                         the callback ID when SPL is not available. Default empty.
     * @param callable|bool $function_to_check Optional. The callback to check for. Default false.
     * @return bool|int The priority of that hook is returned, or false if the function is not attached.
     * @since 4.7.0
     *
     */
    public function has_filter( $tag = '', $function_to_check = false )
    {
        if ( false === $function_to_check ) {
            return $this->has_filters();
        }

        $function_key = _wp_filter_build_unique_id( $tag, $function_to_check, false );
        if ( !$function_key ) {
            return false;
        }

        foreach ( $this->callbacks as $priority => $callbacks ) {
            if ( isset( $callbacks[ $function_key ] ) ) {
                return $priority;
            }
        }

        return false;
    }

    /**
     * Checks if any callbacks have been registered for this hook.
     *
     * @return bool True if callbacks have been registered for the current hook, otherwise false.
     * @since 4.7.0
     *
     */
    public function has_filters()
    {
        foreach ( $this->callbacks as $callbacks ) {
            if ( $callbacks ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Removes all callbacks from the current filter.
     *
     * @param int|bool $priority Optional. The priority number to remove. Default false.
     * @since 4.7.0
     *
     */
    public function remove_all_filters( $priority = false )
    {
        if ( !$this->callbacks ) {
            return;
        }

        if ( false === $priority ) {
            $this->callbacks = [];
        }
        elseif ( isset( $this->callbacks[ $priority ] ) ) {
            unset( $this->callbacks[ $priority ] );
        }

        if ( $this->nesting_level > 0 ) {
            $this->resort_active_iterations();
        }
    }

    /**
     * Calls the callback functions added to a filter hook.
     *
     * @param mixed $value The value to filter.
     * @param array $args Arguments to pass to callbacks.
     * @return mixed The filtered value after all hooked functions are applied to it.
     * @since 4.7.0
     *
     */
    public function apply_filters( $value, $args )
    {
        if ( !$this->callbacks ) {
            return $value;
        }

        $nesting_level = $this->nesting_level++;

        $this->iterations[ $nesting_level ] = array_keys( $this->callbacks );
        $num_args = count( $args );

        do {
            $this->current_priority[ $nesting_level ] = $priority = current( $this->iterations[ $nesting_level ] );

            foreach ( $this->callbacks[ $priority ] as $the_ ) {
                if ( !$this->doing_action ) {
                    $args[ 0 ] = $value;
                }

                // Avoid the array_slice if possible.
                if ( $the_[ 'accepted_args' ] == 0 ) {
                    $value = call_user_func_array( $the_[ 'function' ], [] );
                }
                elseif ( $the_[ 'accepted_args' ] >= $num_args ) {
                    $value = call_user_func_array( $the_[ 'function' ], $args );
                }
                else {
                    $value = call_user_func_array( $the_[ 'function' ], array_slice( $args, 0, (int)$the_[ 'accepted_args' ] ) );
                }
            }
        }
        while ( false !== next( $this->iterations[ $nesting_level ] ) );

        unset( $this->iterations[ $nesting_level ] );
        unset( $this->current_priority[ $nesting_level ] );

        $this->nesting_level--;

        return $value;
    }

    /**
     * Executes the callback functions hooked on a specific action hook.
     *
     * @param mixed $args Arguments to pass to the hook callbacks.
     * @since 4.7.0
     *
     */
    public function do_action( $args )
    {
        $this->doing_action = true;
        $this->apply_filters( '', $args );

        // If there are recursive calls to the current action, we haven't finished it until we get to the last one.
        if ( !$this->nesting_level ) {
            $this->doing_action = false;
        }
    }

    /**
     * Processes the functions hooked into the 'all' hook.
     *
     * @param array $args Arguments to pass to the hook callbacks. Passed by reference.
     * @since 4.7.0
     *
     */
    public function do_all_hook( &$args )
    {
        $nesting_level = $this->nesting_level++;
        $this->iterations[ $nesting_level ] = array_keys( $this->callbacks );

        do {
            $priority = current( $this->iterations[ $nesting_level ] );
            foreach ( $this->callbacks[ $priority ] as $the_ ) {
                call_user_func_array( $the_[ 'function' ], $args );
            }
        }
        while ( false !== next( $this->iterations[ $nesting_level ] ) );

        unset( $this->iterations[ $nesting_level ] );
        $this->nesting_level--;
    }

    /**
     * Return the current priority level of the currently running iteration of the hook.
     *
     * @return int|false If the hook is running, return the current priority level. If it isn't running, return false.
     * @since 4.7.0
     *
     */
    public function current_priority()
    {
        if ( false === current( $this->iterations ) ) {
            return false;
        }

        return current( current( $this->iterations ) );
    }

    /**
     * Normalizes filters set up before WordPress has initialized to WP_Hook objects.
     *
     * @param array $filters Filters to normalize.
     * @return WP_Hook[] Array of normalized filters.
     * @since 4.7.0
     *
     */
    public static function build_preinitialized_hooks( $filters )
    {
        /** @var WP_Hook[] $normalized */
        $normalized = [];

        foreach ( $filters as $tag => $callback_groups ) {
            if ( is_object( $callback_groups ) && $callback_groups instanceof WP_Hook ) {
                $normalized[ $tag ] = $callback_groups;
                continue;
            }
            $hook = new WP_Hook();

            // Loop through callback groups.
            foreach ( $callback_groups as $priority => $callbacks ) {

                // Loop through callbacks.
                foreach ( $callbacks as $cb ) {
                    $hook->add_filter( $tag, $cb[ 'function' ], $priority, $cb[ 'accepted_args' ] );
                }
            }
            $normalized[ $tag ] = $hook;
        }
        return $normalized;
    }

    /**
     * Determines whether an offset value exists.
     *
     * @param mixed $offset An offset to check for.
     * @return bool True if the offset exists, false otherwise.
     * @since 4.7.0
     *
     * @link https://secure.php.net/manual/en/arrayaccess.offsetexists.php
     *
     */
    public function offsetExists( $offset )
    {
        return isset( $this->callbacks[ $offset ] );
    }

    /**
     * Retrieves a value at a specified offset.
     *
     * @param mixed $offset The offset to retrieve.
     * @return mixed If set, the value at the specified offset, null otherwise.
     * @since 4.7.0
     *
     * @link https://secure.php.net/manual/en/arrayaccess.offsetget.php
     *
     */
    public function offsetGet( $offset )
    {
        return isset( $this->callbacks[ $offset ] ) ? $this->callbacks[ $offset ] : null;
    }

    /**
     * Sets a value at a specified offset.
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     * @since 4.7.0
     *
     * @link https://secure.php.net/manual/en/arrayaccess.offsetset.php
     *
     */
    public function offsetSet( $offset, $value )
    {
        if ( is_null( $offset ) ) {
            $this->callbacks[] = $value;
        }
        else {
            $this->callbacks[ $offset ] = $value;
        }
    }

    /**
     * Unsets a specified offset.
     *
     * @param mixed $offset The offset to unset.
     * @link https://secure.php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @since 4.7.0
     *
     */
    public function offsetUnset( $offset )
    {
        unset( $this->callbacks[ $offset ] );
    }

    /**
     * Returns the current element.
     *
     * @return array Of callbacks at current priority.
     * @link https://secure.php.net/manual/en/iterator.current.php
     *
     * @since 4.7.0
     *
     */
    public function current()
    {
        return current( $this->callbacks );
    }

    /**
     * Moves forward to the next element.
     *
     * @return array Of callbacks at next priority.
     * @link https://secure.php.net/manual/en/iterator.next.php
     *
     * @since 4.7.0
     *
     */
    public function next()
    {
        return next( $this->callbacks );
    }

    /**
     * Returns the key of the current element.
     *
     * @return mixed Returns current priority on success, or NULL on failure
     * @link https://secure.php.net/manual/en/iterator.key.php
     *
     * @since 4.7.0
     *
     */
    public function key()
    {
        return key( $this->callbacks );
    }

    /**
     * Checks if current position is valid.
     *
     * @return boolean
     * @link https://secure.php.net/manual/en/iterator.valid.php
     *
     * @since 4.7.0
     *
     */
    public function valid()
    {
        return key( $this->callbacks ) !== null;
    }

    /**
     * Rewinds the Iterator to the first element.
     *
     * @since 4.7.0
     *
     * @link https://secure.php.net/manual/en/iterator.rewind.php
     */
    public function rewind()
    {
        reset( $this->callbacks );
    }

}

/** @var WP_Hook[] $wp_filter */
global $wp_filter, $wp_actions, $wp_current_filter;

if ( $wp_filter ) {
    $wp_filter = WP_Hook::build_preinitialized_hooks( $wp_filter );
}
else {
    $wp_filter = [];
}

if ( !isset( $wp_actions ) ) {
    $wp_actions = [];
}

if ( !isset( $wp_current_filter ) ) {
    $wp_current_filter = [];
}

/**
 * Hook a function or method to a specific filter action.
 *
 * WordPress offers filter hooks to allow plugins to modify
 * various types of internal data at runtime.
 *
 * A plugin can modify data by binding a callback to a filter hook. When the filter
 * is later applied, each bound callback is run in order of priority, and given
 * the opportunity to modify a value by returning a new value.
 *
 * The following example shows how a callback function is bound to a filter hook.
 *
 * Note that `$example` is passed to the callback, (maybe) modified, then returned:
 *
 *     function example_callback( $example ) {
 *         // Maybe modify $example in some way.
 *         return $example;
 *     }
 *     add_filter( 'example_filter', 'example_callback' );
 *
 * Bound callbacks can accept from none to the total number of arguments passed as parameters
 * in the corresponding apply_filters() call.
 *
 * In other words, if an apply_filters() call passes four total arguments, callbacks bound to
 * it can accept none (the same as 1) of the arguments or up to four. The important part is that
 * the `$accepted_args` value must reflect the number of arguments the bound callback *actually*
 * opted to accept. If no arguments were accepted by the callback that is considered to be the
 * same as accepting 1 argument. For example:
 *
 *     // Filter call.
 *     $value = apply_filters( 'hook', $value, $arg2, $arg3 );
 *
 *     // Accepting zero/one arguments.
 *     function example_callback() {
 *         ...
 *         return 'some value';
 *     }
 *     add_filter( 'hook', 'example_callback' ); // Where $priority is default 10, $accepted_args is default 1.
 *
 *     // Accepting two arguments (three possible).
 *     function example_callback( $value, $arg2 ) {
 *         ...
 *         return $maybe_modified_value;
 *     }
 *     add_filter( 'hook', 'example_callback', 10, 2 ); // Where $priority is 10, $accepted_args is 2.
 *
 * *Note:* The function will return true whether or not the callback is valid.
 * It is up to you to take care. This is done for optimization purposes, so
 * everything is as quick as possible.
 *
 * @param string $tag The name of the filter to hook the $function_to_add callback to.
 * @param callable $function_to_add The callback to be run when the filter is applied.
 * @param int $priority Optional. Used to specify the order in which the functions
 *                                  associated with a particular action are executed. Default 10.
 *                                  Lower numbers correspond with earlier execution,
 *                                  and functions with the same priority are executed
 *                                  in the order in which they were added to the action.
 * @param int $accepted_args Optional. The number of arguments the function accepts. Default 1.
 * @return true
 * @global array $wp_filter A multidimensional array of all hooks and the callbacks hooked to them.
 *
 * @since 0.71
 *
 */
function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 )
{
    global $wp_filter;
    if ( !isset( $wp_filter[ $tag ] ) ) {
        $wp_filter[ $tag ] = new WP_Hook();
    }
    $wp_filter[ $tag ]->add_filter( $tag, $function_to_add, $priority, $accepted_args );
    return true;
}

/**
 * Check if any filter has been registered for a hook.
 *
 * @param string $tag The name of the filter hook.
 * @param callable|bool $function_to_check Optional. The callback to check for. Default false.
 * @return false|int If $function_to_check is omitted, returns boolean for whether the hook has
 *                   anything registered. When checking a specific function, the priority of that
 *                   hook is returned, or false if the function is not attached. When using the
 *                   $function_to_check argument, this function may return a non-boolean value
 *                   that evaluates to false (e.g.) 0, so use the === operator for testing the
 *                   return value.
 * @global array $wp_filter Stores all of the filters.
 *
 * @since 2.5.0
 *
 */
function has_filter( $tag, $function_to_check = false )
{
    global $wp_filter;

    if ( !isset( $wp_filter[ $tag ] ) ) {
        return false;
    }

    return $wp_filter[ $tag ]->has_filter( $tag, $function_to_check );
}

/**
 * Call the functions added to a filter hook.
 *
 * The callback functions attached to filter hook $tag are invoked by calling
 * this function. This function can be used to create a new filter hook by
 * simply calling this function with the name of the new hook specified using
 * the $tag parameter.
 *
 * The function allows for additional arguments to be added and passed to hooks.
 *
 *     // Our filter callback function
 *     function example_callback( $string, $arg1, $arg2 ) {
 *         // (maybe) modify $string
 *         return $string;
 *     }
 *     add_filter( 'example_filter', 'example_callback', 10, 3 );
 *
 *     /*
 *      * Apply the filters by calling the 'example_callback' function we
 *      * "hooked" to 'example_filter' using the add_filter() function above.
 *      * - 'example_filter' is the filter hook $tag
 *      * - 'filter me' is the value being filtered
 *      * - $arg1 and $arg2 are the additional arguments passed to the callback.
 *     $value = apply_filters( 'example_filter', 'filter me', $arg1, $arg2 );
 *
 * @param string $tag The name of the filter hook.
 * @param mixed $value The value on which the filters hooked to `$tag` are applied on.
 * @param mixed $var,... Additional variables passed to the functions hooked to `$tag`.
 * @return mixed The filtered value after all hooked functions are applied to it.
 * @global array $wp_filter Stores all of the filters.
 * @global array $wp_current_filter Stores the list of current filters with the current one last.
 *
 * @since 0.71
 *
 */
function apply_filters( $tag, $value )
{
    global $wp_filter, $wp_current_filter;

    $args = [];

    // Do 'all' actions first.
    if ( isset( $wp_filter[ 'all' ] ) ) {
        $wp_current_filter[] = $tag;
        $args = func_get_args();
        _wp_call_all_hook( $args );
    }

    if ( !isset( $wp_filter[ $tag ] ) ) {
        if ( isset( $wp_filter[ 'all' ] ) ) {
            array_pop( $wp_current_filter );
        }
        return $value;
    }

    if ( !isset( $wp_filter[ 'all' ] ) ) {
        $wp_current_filter[] = $tag;
    }

    if ( empty( $args ) ) {
        $args = func_get_args();
    }

    // don't pass the tag name to WP_Hook
    array_shift( $args );

    $filtered = $wp_filter[ $tag ]->apply_filters( $value, $args );

    array_pop( $wp_current_filter );

    return $filtered;
}

/**
 * Execute functions hooked on a specific filter hook, specifying arguments in an array.
 *
 * @param string $tag The name of the filter hook.
 * @param array $args The arguments supplied to the functions hooked to $tag.
 * @return mixed The filtered value after all hooked functions are applied to it.
 * @global array $wp_current_filter Stores the list of current filters with the current one last
 *
 * @since 3.0.0
 *
 * @see apply_filters() This function is identical, but the arguments passed to the
 * functions hooked to `$tag` are supplied using an array.
 *
 * @global array $wp_filter Stores all of the filters
 */
function apply_filters_ref_array( $tag, $args )
{
    global $wp_filter, $wp_current_filter;

    // Do 'all' actions first
    if ( isset( $wp_filter[ 'all' ] ) ) {
        $wp_current_filter[] = $tag;
        $all_args = func_get_args();
        _wp_call_all_hook( $all_args );
    }

    if ( !isset( $wp_filter[ $tag ] ) ) {
        if ( isset( $wp_filter[ 'all' ] ) ) {
            array_pop( $wp_current_filter );
        }
        return $args[ 0 ];
    }

    if ( !isset( $wp_filter[ 'all' ] ) ) {
        $wp_current_filter[] = $tag;
    }

    $filtered = $wp_filter[ $tag ]->apply_filters( $args[ 0 ], $args );

    array_pop( $wp_current_filter );

    return $filtered;
}

/**
 * Removes a function from a specified filter hook.
 *
 * This function removes a function attached to a specified filter hook. This
 * method can be used to remove default functions attached to a specific filter
 * hook and possibly replace them with a substitute.
 *
 * To remove a hook, the $function_to_remove and $priority arguments must match
 * when the hook was added. This goes for both filters and actions. No warning
 * will be given on removal failure.
 *
 * @param string $tag The filter hook to which the function to be removed is hooked.
 * @param callable $function_to_remove The name of the function which should be removed.
 * @param int $priority Optional. The priority of the function. Default 10.
 * @return bool    Whether the function existed before it was removed.
 * @since 1.2.0
 *
 * @global array $wp_filter Stores all of the filters
 *
 */
function remove_filter( $tag, $function_to_remove, $priority = 10 )
{
    global $wp_filter;

    $r = false;
    if ( isset( $wp_filter[ $tag ] ) ) {
        $r = $wp_filter[ $tag ]->remove_filter( $tag, $function_to_remove, $priority );
        if ( !$wp_filter[ $tag ]->callbacks ) {
            unset( $wp_filter[ $tag ] );
        }
    }

    return $r;
}

/**
 * Remove all of the hooks from a filter.
 *
 * @param string $tag The filter to remove hooks from.
 * @param int|bool $priority Optional. The priority number to remove. Default false.
 * @return true True when finished.
 * @global array $wp_filter Stores all of the filters
 *
 * @since 2.7.0
 *
 */
function remove_all_filters( $tag, $priority = false )
{
    global $wp_filter;

    if ( isset( $wp_filter[ $tag ] ) ) {
        $wp_filter[ $tag ]->remove_all_filters( $priority );
        if ( !$wp_filter[ $tag ]->has_filters() ) {
            unset( $wp_filter[ $tag ] );
        }
    }

    return true;
}

/**
 * Retrieve the name of the current filter or action.
 *
 * @return string Hook name of the current filter or action.
 * @global array $wp_current_filter Stores the list of current filters with the current one last
 *
 * @since 2.5.0
 *
 */
function current_filter()
{
    global $wp_current_filter;
    return end( $wp_current_filter );
}

/**
 * Retrieve the name of the current action.
 *
 * @return string Hook name of the current action.
 * @since 3.9.0
 *
 */
function current_action()
{
    return current_filter();
}

/**
 * Retrieve the name of a filter currently being processed.
 *
 * The function current_filter() only returns the most recent filter or action
 * being executed. did_action() returns true once the action is initially
 * processed.
 *
 * This function allows detection for any filter currently being
 * executed (despite not being the most recent filter to fire, in the case of
 * hooks called from hook callbacks) to be verified.
 *
 * @param null|string $filter Optional. Filter to check. Defaults to null, which
 *                            checks if any filter is currently being run.
 * @return bool Whether the filter is currently in the stack.
 * @see did_action()
 * @global array $wp_current_filter Current filter.
 *
 * @since 3.9.0
 *
 * @see current_filter()
 */
function doing_filter( $filter = null )
{
    global $wp_current_filter;

    if ( null === $filter ) {
        return !empty( $wp_current_filter );
    }

    return in_array( $filter, $wp_current_filter );
}

/**
 * Retrieve the name of an action currently being processed.
 *
 * @param string|null $action Optional. Action to check. Defaults to null, which checks
 *                            if any action is currently being run.
 * @return bool Whether the action is currently in the stack.
 * @since 3.9.0
 *
 */
function doing_action( $action = null )
{
    return doing_filter( $action );
}

/**
 * Hooks a function on to a specific action.
 *
 * Actions are the hooks that the WordPress core launches at specific points
 * during execution, or when specific events occur. Plugins can specify that
 * one or more of its PHP functions are executed at these points, using the
 * Action API.
 *
 * @param string $tag The name of the action to which the $function_to_add is hooked.
 * @param callable $function_to_add The name of the function you wish to be called.
 * @param int $priority Optional. Used to specify the order in which the functions
 *                                  associated with a particular action are executed. Default 10.
 *                                  Lower numbers correspond with earlier execution,
 *                                  and functions with the same priority are executed
 *                                  in the order in which they were added to the action.
 * @param int $accepted_args Optional. The number of arguments the function accepts. Default 1.
 * @return true Will always return true.
 * @since 1.2.0
 *
 */
function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 )
{
    return add_filter( $tag, $function_to_add, $priority, $accepted_args );
}

/**
 * Execute functions hooked on a specific action hook.
 *
 * This function invokes all functions attached to action hook `$tag`. It is
 * possible to create new action hooks by simply calling this function,
 * specifying the name of the new hook using the `$tag` parameter.
 *
 * You can pass extra arguments to the hooks, much like you can with apply_filters().
 *
 * @param string $tag The name of the action to be executed.
 * @param mixed $arg,... Optional. Additional arguments which are passed on to the
 *                        functions hooked to the action. Default empty.
 * @global array $wp_actions Increments the amount of times action was triggered.
 * @global array $wp_current_filter Stores the list of current filters with the current one last
 *
 * @since 1.2.0
 *
 * @global array $wp_filter Stores all of the filters
 */
function do_action( $tag, $arg = '' )
{
    global $wp_filter, $wp_actions, $wp_current_filter;

    if ( !isset( $wp_actions[ $tag ] ) ) {
        $wp_actions[ $tag ] = 1;
    }
    else {
        ++$wp_actions[ $tag ];
    }

    // Do 'all' actions first
    if ( isset( $wp_filter[ 'all' ] ) ) {
        $wp_current_filter[] = $tag;
        $all_args = func_get_args();
        _wp_call_all_hook( $all_args );
    }

    if ( !isset( $wp_filter[ $tag ] ) ) {
        if ( isset( $wp_filter[ 'all' ] ) ) {
            array_pop( $wp_current_filter );
        }
        return;
    }

    if ( !isset( $wp_filter[ 'all' ] ) ) {
        $wp_current_filter[] = $tag;
    }

    $args = [];
    if ( is_array( $arg ) && 1 == count( $arg ) && isset( $arg[ 0 ] ) && is_object( $arg[ 0 ] ) ) { // array(&$this)
        $args[] =& $arg[ 0 ];
    }
    else {
        $args[] = $arg;
    }
    for ( $a = 2, $num = func_num_args(); $a < $num; $a++ ) {
        $args[] = func_get_arg( $a );
    }

    $wp_filter[ $tag ]->do_action( $args );

    array_pop( $wp_current_filter );
}

/**
 * Retrieve the number of times an action is fired.
 *
 * @param string $tag The name of the action hook.
 * @return int The number of times action hook $tag is fired.
 * @since 2.1.0
 *
 * @global array $wp_actions Increments the amount of times action was triggered.
 *
 */
function did_action( $tag )
{
    global $wp_actions;

    if ( !isset( $wp_actions[ $tag ] ) ) {
        return 0;
    }

    return $wp_actions[ $tag ];
}

/**
 * Execute functions hooked on a specific action hook, specifying arguments in an array.
 *
 * @param string $tag The name of the action to be executed.
 * @param array $args The arguments supplied to the functions hooked to `$tag`.
 * @global array $wp_filter Stores all of the filters
 * @global array $wp_actions Increments the amount of times action was triggered.
 * @global array $wp_current_filter Stores the list of current filters with the current one last
 *
 * @since 2.1.0
 *
 * @see do_action() This function is identical, but the arguments passed to the
 *                  functions hooked to $tag< are supplied using an array.
 */
function do_action_ref_array( $tag, $args )
{
    global $wp_filter, $wp_actions, $wp_current_filter;

    if ( !isset( $wp_actions[ $tag ] ) ) {
        $wp_actions[ $tag ] = 1;
    }
    else {
        ++$wp_actions[ $tag ];
    }

    // Do 'all' actions first
    if ( isset( $wp_filter[ 'all' ] ) ) {
        $wp_current_filter[] = $tag;
        $all_args = func_get_args();
        _wp_call_all_hook( $all_args );
    }

    if ( !isset( $wp_filter[ $tag ] ) ) {
        if ( isset( $wp_filter[ 'all' ] ) ) {
            array_pop( $wp_current_filter );
        }
        return;
    }

    if ( !isset( $wp_filter[ 'all' ] ) ) {
        $wp_current_filter[] = $tag;
    }

    $wp_filter[ $tag ]->do_action( $args );

    array_pop( $wp_current_filter );
}

/**
 * Check if any action has been registered for a hook.
 *
 * @param string $tag The name of the action hook.
 * @param callable|bool $function_to_check Optional. The callback to check for. Default false.
 * @return bool|int If $function_to_check is omitted, returns boolean for whether the hook has
 *                  anything registered. When checking a specific function, the priority of that
 *                  hook is returned, or false if the function is not attached. When using the
 *                  $function_to_check argument, this function may return a non-boolean value
 *                  that evaluates to false (e.g.) 0, so use the === operator for testing the
 *                  return value.
 * @see has_filter() has_action() is an alias of has_filter().
 *
 * @since 2.5.0
 *
 */
function has_action( $tag, $function_to_check = false )
{
    return has_filter( $tag, $function_to_check );
}

/**
 * Removes a function from a specified action hook.
 *
 * This function removes a function attached to a specified action hook. This
 * method can be used to remove default functions attached to a specific filter
 * hook and possibly replace them with a substitute.
 *
 * @param string $tag The action hook to which the function to be removed is hooked.
 * @param callable $function_to_remove The name of the function which should be removed.
 * @param int $priority Optional. The priority of the function. Default 10.
 * @return bool Whether the function is removed.
 * @since 1.2.0
 *
 */
function remove_action( $tag, $function_to_remove, $priority = 10 )
{
    return remove_filter( $tag, $function_to_remove, $priority );
}

/**
 * Remove all of the hooks from an action.
 *
 * @param string $tag The action to remove hooks from.
 * @param int|bool $priority The priority number to remove them from. Default false.
 * @return true True when finished.
 * @since 2.7.0
 *
 */
function remove_all_actions( $tag, $priority = false )
{
    return remove_all_filters( $tag, $priority );
}

/**
 * Fires functions attached to a deprecated filter hook.
 *
 * When a filter hook is deprecated, the apply_filters() call is replaced with
 * apply_filters_deprecated(), which triggers a deprecation notice and then fires
 * the original filter hook.
 *
 * Note: the value and extra arguments passed to the original apply_filters() call
 * must be passed here to `$args` as an array. For example:
 *
 *     // Old filter.
 *     return apply_filters( 'wpdocs_filter', $value, $extra_arg );
 *
 *     // Deprecated.
 *     return apply_filters_deprecated( 'wpdocs_filter', array( $value, $extra_arg ), '4.9', 'wpdocs_new_filter' );
 *
 * @param string $tag The name of the filter hook.
 * @param array $args Array of additional function arguments to be passed to apply_filters().
 * @param string $version The version of WordPress that deprecated the hook.
 * @param string $replacement Optional. The hook that should have been used. Default false.
 * @param string $message Optional. A message regarding the change. Default null.
 * @see _deprecated_hook()
 *
 * @since 4.6.0
 *
 */
function apply_filters_deprecated( $tag, $args, $version, $replacement = false, $message = null )
{
    if ( !has_filter( $tag ) ) {
        return $args[ 0 ];
    }

    _deprecated_hook( $tag, $version, $replacement, $message );

    return apply_filters_ref_array( $tag, $args );
}

/**
 * Fires functions attached to a deprecated action hook.
 *
 * When an action hook is deprecated, the do_action() call is replaced with
 * do_action_deprecated(), which triggers a deprecation notice and then fires
 * the original hook.
 *
 * @param string $tag The name of the action hook.
 * @param array $args Array of additional function arguments to be passed to do_action().
 * @param string $version The version of WordPress that deprecated the hook.
 * @param string $replacement Optional. The hook that should have been used.
 * @param string $message Optional. A message regarding the change.
 * @see _deprecated_hook()
 *
 * @since 4.6.0
 *
 */
function do_action_deprecated( $tag, $args, $version, $replacement = false, $message = null )
{
    if ( !has_action( $tag ) ) {
        return;
    }

    _deprecated_hook( $tag, $version, $replacement, $message );

    do_action_ref_array( $tag, $args );
}

//==========================================================

/**
 * Call the 'all' hook, which will process the functions hooked into it.
 *
 * The 'all' hook passes all of the arguments or parameters that were used for
 * the hook, which this function was called for.
 *
 * This function is used internally for apply_filters(), do_action(), and
 * do_action_ref_array() and is not meant to be used from outside those
 * functions. This function does not check for the existence of the all hook, so
 * it will fail unless the all hook exists prior to this function call.
 *
 * @since 2.5.0
 * @access private
 *
 * @global array $wp_filter  Stores all of the filters
 *
 * @param array $args The collected parameters from the hook that was called.
 */
function _wp_call_all_hook( $args ) {
    global $wp_filter;

    $wp_filter['all']->do_all_hook( $args );
}

/**
 * Marks a deprecated action or filter hook as deprecated and throws a notice.
 *
 * Use the {@see 'deprecated_hook_run'} action to get the backtrace describing where
 * the deprecated hook was called.
 *
 * Default behavior is to trigger a user error if `WP_DEBUG` is true.
 *
 * This function is called by the do_action_deprecated() and apply_filters_deprecated()
 * functions, and so generally does not need to be called directly.
 *
 * @since 4.6.0
 * @access private
 *
 * @param string $hook        The hook that was used.
 * @param string $version     The version of WordPress that deprecated the hook.
 * @param string $replacement Optional. The hook that should have been used.
 * @param string $message     Optional. A message regarding the change.
 */
function _deprecated_hook( $hook, $version, $replacement = null, $message = null ) {
    /**
     * Fires when a deprecated hook is called.
     *
     * @since 4.6.0
     *
     * @param string $hook        The hook that was called.
     * @param string $replacement The hook that should be used as a replacement.
     * @param string $version     The version of WordPress that deprecated the argument used.
     * @param string $message     A message regarding the change.
     */
    do_action( 'deprecated_hook_run', $hook, $replacement, $version, $message );

    /**
     * Filters whether to trigger deprecated hook errors.
     *
     * @since 4.6.0
     *
     * @param bool $trigger Whether to trigger deprecated hook errors. Requires
     *                      `WP_DEBUG` to be defined true.
     */
    if ( apply_filters( 'deprecated_hook_trigger_error', true ) ) {
        $message = empty( $message ) ? '' : ' ' . $message;
        if ( ! is_null( $replacement ) ) {
            /* translators: 1: WordPress hook name, 2: version number, 3: alternative hook name */
            trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.' ), $hook, $version, $replacement ) . $message );
        } else {
            /* translators: 1: WordPress hook name, 2: version number */
            trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.' ), $hook, $version ) . $message );
        }
    }
}

/**
 * Build Unique ID for storage and retrieval.
 *
 * The old way to serialize the callback caused issues and this function is the
 * solution. It works by checking for objects and creating a new property in
 * the class to keep track of the object and new objects of the same class that
 * need to be added.
 *
 * It also allows for the removal of actions and filters for objects after they
 * change class properties. It is possible to include the property $wp_filter_id
 * in your class and set it to "null" or a number to bypass the workaround.
 * However this will prevent you from adding new classes and any new classes
 * will overwrite the previous hook by the same class.
 *
 * Functions and static method callbacks are just returned as strings and
 * shouldn't have any speed penalty.
 *
 * @link https://core.trac.wordpress.org/ticket/3875
 *
 * @since 2.2.3
 * @access private
 *
 * @global array $wp_filter Storage for all of the filters and actions.
 * @staticvar int $filter_id_count
 *
 * @param string $tag Used in counting how many hooks were applied
 * @param callable $function Used for creating unique id
 * @param int|bool $priority Used in counting how many hooks were applied. If === false
 *                           and $function is an object reference, we return the unique
 *                           id only if it already has one, false otherwise.
 * @return string|false Unique ID for usage as array key or false if $priority === false
 *                      and $function is an object reference, and it does not already have
 *                      a unique id.
 */
function _wp_filter_build_unique_id( $tag, $function, $priority )
{
    global $wp_filter;
    static $filter_id_count = 0;

    if ( is_string( $function ) ) {
        return $function;
    }

    if ( is_object( $function ) ) {
        // Closures are currently implemented as objects
        $function = [ $function, '' ];
    }
    else {
        $function = (array)$function;
    }

    if ( is_object( $function[ 0 ] ) ) {
        // Object Class Calling
        if ( function_exists( 'spl_object_hash' ) ) {
            return spl_object_hash( $function[ 0 ] ) . $function[ 1 ];
        }
        else {
            $obj_idx = get_class( $function[ 0 ] ) . $function[ 1 ];
            if ( !isset( $function[ 0 ]->wp_filter_id ) ) {
                if ( false === $priority ) {
                    return false;
                }
                $obj_idx .= isset( $wp_filter[ $tag ][ $priority ] ) ? count( (array)$wp_filter[ $tag ][ $priority ] ) : $filter_id_count;
                $function[ 0 ]->wp_filter_id = $filter_id_count;
                ++$filter_id_count;
            }
            else {
                $obj_idx .= $function[ 0 ]->wp_filter_id;
            }

            return $obj_idx;
        }
    }
    elseif ( is_string( $function[ 0 ] ) ) {
        // Static Calling
        return $function[ 0 ] . '::' . $function[ 1 ];
    }
    return false;
}
