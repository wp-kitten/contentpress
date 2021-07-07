<?php
/*
 * Helper functions imported from WordPress
 */

use App\Helpers\WP\WP_Error;

/**
 * Serialize data, if needed.
 *
 * @param string|array|object $data Data that might be serialized.
 * @return mixed A scalar data
 * @since 2.0.5
 *
 */
function maybe_serialize( $data )
{
    if ( is_array( $data ) || is_object( $data ) ) {
        return serialize( $data );
    }

    // Double serialization is required for backward compatibility.
    // See https://core.trac.wordpress.org/ticket/12930
    // Also the world will end. See WP 3.6.1.
    if ( is_serialized( $data, false ) ) {
        return serialize( $data );
    }
    return $data;
}

/**
 * Unserialize value only if it was serialized.
 *
 * @param string $original Maybe unserialized original, if is needed.
 * @return mixed Unserialized data can be any type.
 * @since 2.0.0
 *
 */
function maybe_unserialize( $original )
{
    if ( is_serialized( $original ) ) { // don't attempt to unserialize data that wasn't serialized going in
        return @unserialize( $original );
    }
    return $original;
}

/**
 * Check value to find if it was serialized.
 *
 * If $data is not an string, then returned value will always be false.
 * Serialized data is always a string.
 *
 * @param string $data Value to check to see if was serialized.
 * @param bool $strict Optional. Whether to be strict about the end of the string. Default true.
 * @return bool False if not serialized and true if it was.
 * @since 2.0.5
 *
 */
function is_serialized( $data, $strict = true )
{
    // if it isn't a string, it isn't serialized.
    if ( !is_string( $data ) ) {
        return false;
    }
    $data = trim( $data );
    if ( 'N;' == $data ) {
        return true;
    }
    if ( strlen( $data ) < 4 ) {
        return false;
    }
    if ( ':' !== $data[ 1 ] ) {
        return false;
    }
    if ( $strict ) {
        $lastc = substr( $data, -1 );
        if ( ';' !== $lastc && '}' !== $lastc ) {
            return false;
        }
    }
    else {
        $semicolon = strpos( $data, ';' );
        $brace = strpos( $data, '}' );
        // Either ; or } must exist.
        if ( false === $semicolon && false === $brace ) {
            return false;
        }
        // But neither must be in the first X characters.
        if ( false !== $semicolon && $semicolon < 3 ) {
            return false;
        }
        if ( false !== $brace && $brace < 4 ) {
            return false;
        }
    }
    $token = $data[ 0 ];
    switch ( $token ) {
        case 's':
            if ( $strict ) {
                if ( '"' !== substr( $data, -2, 1 ) ) {
                    return false;
                }
            }
            elseif ( false === strpos( $data, '"' ) ) {
                return false;
            }
            return false;
        // or else fall through
        case 'a':
        case 'O':
            return (bool)preg_match( "/^{$token}:[0-9]+:/s", $data );
        case 'b':
        case 'i':
        case 'd':
            $end = $strict ? '$' : '';
            return (bool)preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
        default:
            return false;
    }
}

/**
 * Normalize a filesystem path.
 *
 * On windows systems, replaces backslashes with forward slashes
 * and forces upper-case drive letters.
 * Allows for two leading slashes for Windows network shares, but
 * ensures that all other duplicate slashes are reduced to a single.
 *
 * @param string $path Path to normalize.
 * @return string Normalized path.
 * @since 4.5.0 Allows for Windows network shares.
 * @since 4.9.7 Allows for PHP file wrappers.
 *
 * @since 3.9.0
 * @since 4.4.0 Ensures upper-case drive letters on Windows systems.
 */
function wp_normalize_path( $path )
{
    $wrapper = '';
    if ( wp_is_stream( $path ) ) {
        [ $wrapper, $path ] = explode( '://', $path, 2 );
        $wrapper .= '://';
    }

    // Standardise all paths to use /
    $path = str_replace( '\\', '/', $path );

    // Replace multiple slashes down to a singular, allowing for network shares having two slashes.
    $path = preg_replace( '|(?<=.)/+|', '/', $path );

    // Windows paths should uppercase the drive letter
    if ( ':' === substr( $path, 1, 1 ) ) {
        $path = ucfirst( $path );
    }

    return $wrapper . $path;
}

/**
 * Test if a given path is a stream URL
 *
 * @param string $path The resource path or URL.
 * @return bool True if the path is a stream URL.
 * @since 3.5.0
 *
 */
function wp_is_stream( $path )
{
    $scheme_separator = strpos( $path, '://' );

    if ( false === $scheme_separator ) {
        // $path isn't a stream
        return false;
    }

    $stream = substr( $path, 0, $scheme_separator );

    return in_array( $stream, stream_get_wrappers(), true );
}

/**
 * Check whether variable is a ValPress Error.
 *
 * Returns true if $thing is an object of the WP_Error class.
 *
 * @param mixed $thing Check if unknown variable is a WP_Error object.
 * @return bool True, if WP_Error. False, if not WP_Error.
 * @since WordPress 2.1.0
 *
 */
function is_wp_error( $thing )
{
    return ( $thing instanceof WP_Error );
}

/**
 * Retrieve a list of protocols to allow in HTML attributes.
 *
 * @return string[] Array of allowed protocols. Defaults to an array containing 'http', 'https',
 *                  'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet',
 *                  'mms', 'rtsp', 'sms', 'svn', 'tel', 'fax', 'xmpp', 'webcal', and 'urn'.
 *                  This covers all common link protocols, except for 'javascript' which should not
 *                  be allowed for untrusted users.
 * @since 4.3.0 Added 'webcal' to the protocols array.
 * @since 4.7.0 Added 'urn' to the protocols array.
 * @since 5.3.0 Added 'sms' to the protocols array.
 *
 * @see wp_kses()
 * @see esc_url()
 *
 * @staticvar array $protocols
 *
 * @since 3.3.0
 */
function wp_allowed_protocols()
{
    static $protocols = [];

    if ( empty( $protocols ) ) {
        $protocols = [ 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'sms', 'svn', 'tel', 'fax', 'xmpp', 'webcal', 'urn' ];
    }

    if ( !did_action( 'wp_loaded' ) ) {
        /**
         * Filters the list of protocols allowed in HTML attributes.
         *
         * @param array $protocols Array of allowed protocols e.g. 'http', 'ftp', 'tel', and more.
         * @since 3.0.0
         *
         */
        $protocols = array_unique( (array)apply_filters( 'kses_allowed_protocols', $protocols ) );
    }

    return $protocols;
}

/**
 * Escaping for HTML attributes.
 *
 * @param string $text
 * @return string
 * @since 2.8.0
 *
 */
function esc_attr( $text )
{
    $safe_text = wp_check_invalid_utf8( $text );
    $safe_text = _wp_specialchars( $safe_text, ENT_QUOTES );
    /**
     * Filters a string cleaned and escaped for output in an HTML attribute.
     *
     * Text passed to esc_attr() is stripped of invalid or special characters
     * before output.
     *
     * @param string $safe_text The text after it has been escaped.
     * @param string $text The text prior to being escaped.
     * @since 2.0.6
     *
     */
    return apply_filters( 'attribute_escape', $safe_text, $text );
}

/**
 * Escaping for textarea values.
 *
 * @param string $text
 * @return string
 * @since 3.1.0
 *
 */
function esc_textarea( $text )
{
    $safe_text = htmlspecialchars( $text, ENT_QUOTES, vp_get_charset() );
    /**
     * Filters a string cleaned and escaped for output in a textarea element.
     *
     * @param string $safe_text The text after it has been escaped.
     * @param string $text The text prior to being escaped.
     * @since 3.1.0
     *
     */
    return apply_filters( 'esc_textarea', $safe_text, $text );
}

/**
 * Escape single quotes, htmlspecialchar " < > &, and fix line endings.
 *
 * Escapes text strings for echoing in JS. It is intended to be used for inline JS
 * (in a tag attribute, for example onclick="..."). Note that the strings have to
 * be in single quotes. The {@see 'js_escape'} filter is also applied here.
 *
 * @param string $text The text to be escaped.
 * @return string Escaped text.
 * @since 2.8.0
 *
 */
function esc_js( $text )
{
    $safe_text = wp_check_invalid_utf8( $text );
    $safe_text = _wp_specialchars( $safe_text, ENT_COMPAT );
    $safe_text = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", stripslashes( $safe_text ) );
    $safe_text = str_replace( "\r", '', $safe_text );
    $safe_text = str_replace( "\n", '\\n', addslashes( $safe_text ) );
    /**
     * Filters a string cleaned and escaped for output in JavaScript.
     *
     * Text passed to esc_js() is stripped of invalid or special characters,
     * and properly slashed for output.
     *
     * @param string $safe_text The text after it has been escaped.
     * @param string $text The text prior to being escaped.
     * @since 2.0.6
     *
     */
    return apply_filters( 'js_escape', $safe_text, $text );
}

/**
 * Escaping for HTML blocks.
 *
 * @param string $text
 * @return string
 * @since 2.8.0
 *
 */
function esc_html( $text )
{
    $safe_text = wp_check_invalid_utf8( $text );
    $safe_text = _wp_specialchars( $safe_text, ENT_QUOTES );
    /**
     * Filters a string cleaned and escaped for output in HTML.
     *
     * Text passed to esc_html() is stripped of invalid or special characters
     * before output.
     *
     * @param string $safe_text The text after it has been escaped.
     * @param string $text The text prior to being escaped.
     * @since 2.8.0
     *
     */
    return apply_filters( 'esc_html', $safe_text, $text );
}

/**
 * Checks for invalid UTF8 in a string.
 *
 * @param string $string The text which is to be checked.
 * @param bool $strip Optional. Whether to attempt to strip out invalid UTF8. Default is false.
 * @return string The checked text.
 * @since 2.8.0
 *
 * @staticvar bool $is_utf8
 * @staticvar bool $utf8_pcre
 *
 */
function wp_check_invalid_utf8( $string, $strip = false )
{
    $string = (string)$string;

    if ( 0 === strlen( $string ) ) {
        return '';
    }

    // Store the site charset as a static to avoid multiple calls to get_option().
    static $is_utf8 = null;
    if ( !isset( $is_utf8 ) ) {
        $is_utf8 = in_array( vp_get_charset(), [ 'utf8', 'utf-8', 'UTF8', 'UTF-8' ] );
    }
    if ( !$is_utf8 ) {
        return $string;
    }

    // Check for support for utf8 in the installed PCRE library once and store the result in a static.
    static $utf8_pcre = null;
    if ( !isset( $utf8_pcre ) ) {
        // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
        $utf8_pcre = @preg_match( '/^./u', 'a' );
    }
    // We can't demand utf8 in the PCRE installation, so just return the string in those cases.
    if ( !$utf8_pcre ) {
        return $string;
    }

    // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- preg_match fails when it encounters invalid UTF8 in $string.
    if ( 1 === @preg_match( '/^./us', $string ) ) {
        return $string;
    }

    // Attempt to strip the bad chars if requested (not recommended).
    if ( $strip && function_exists( 'iconv' ) ) {
        return iconv( 'utf-8', 'utf-8', $string );
    }

    return '';
}

/**
 * Converts a number of special characters into their HTML entities.
 *
 * Specifically deals with: &, <, >, ", and '.
 *
 * $quote_style can be set to ENT_COMPAT to encode " to
 * &quot;, or ENT_QUOTES to do both. Default is ENT_NOQUOTES where no quotes are encoded.
 *
 * @param string $string The text which is to be encoded.
 * @param int|string $quote_style Optional. Converts double quotes if set to ENT_COMPAT,
 *                                    both single and double if set to ENT_QUOTES or none if set to ENT_NOQUOTES.
 *                                    Also compatible with old values; converting single quotes if set to 'single',
 *                                    double if set to 'double' or both if otherwise set.
 *                                    Default is ENT_NOQUOTES.
 * @param false|string $charset Optional. The character encoding of the string. Default is false.
 * @param bool $double_encode Optional. Whether to encode existing html entities. Default is false.
 * @return string The encoded text with HTML entities.
 * @since 1.2.2
 * @access private
 *
 * @staticvar string $_charset
 *
 */
function _wp_specialchars( $string, $quote_style = ENT_NOQUOTES, $charset = false, $double_encode = false )
{
    $string = (string)$string;

    if ( 0 === strlen( $string ) ) {
        return '';
    }

    // Don't bother if there are no specialchars - saves some processing.
    if ( !preg_match( '/[&<>"\']/', $string ) ) {
        return $string;
    }

    // Account for the previous behaviour of the function when the $quote_style is not an accepted value.
    if ( empty( $quote_style ) ) {
        $quote_style = ENT_NOQUOTES;
    }
    elseif ( !in_array( $quote_style, [ 0, 2, 3, 'single', 'double' ], true ) ) {
        $quote_style = ENT_QUOTES;
    }

    // Store the site charset as a static to avoid multiple calls to wp_load_alloptions().
    if ( !$charset ) {
        $charset = vp_get_charset();
    }

    if ( in_array( $charset, [ 'utf8', 'utf-8', 'UTF8' ] ) ) {
        $charset = 'UTF-8';
    }

    $_quote_style = $quote_style;

    if ( 'double' === $quote_style ) {
        $quote_style = ENT_COMPAT;
        $_quote_style = ENT_COMPAT;
    }
    elseif ( 'single' === $quote_style ) {
        $quote_style = ENT_NOQUOTES;
    }

    if ( !$double_encode ) {
        // Guarantee every &entity; is valid, convert &garbage; into &amp;garbage;
        // This is required for PHP < 5.4.0 because ENT_HTML401 flag is unavailable.
        $string = wp_kses_normalize_entities( $string );
    }

    $string = htmlspecialchars( $string, $quote_style, $charset, $double_encode );

    // Back-compat.
    if ( 'single' === $_quote_style ) {
        $string = str_replace( "'", '&#039;', $string );
    }

    return $string;
}

/**
 * Maps a function to all non-iterable elements of an array or an object.
 *
 * This is similar to `array_walk_recursive()` but acts upon objects too.
 *
 * @param mixed $value The array, object, or scalar.
 * @param callable $callback The function to map onto $value.
 * @return mixed The value with the callback applied to all non-arrays and non-objects inside it.
 * @since 4.4.0
 *
 */
function map_deep( $value, $callback )
{
    if ( is_array( $value ) ) {
        foreach ( $value as $index => $item ) {
            $value[ $index ] = map_deep( $item, $callback );
        }
    }
    elseif ( is_object( $value ) ) {
        $object_vars = get_object_vars( $value );
        foreach ( $object_vars as $property_name => $property_value ) {
            $value->$property_name = map_deep( $property_value, $callback );
        }
    }
    else {
        $value = call_user_func( $callback, $value );
    }

    return $value;
}

/**
 * Parses a string into variables to be stored in an array.
 *
 *
 * @param string $string The string to be parsed.
 * @param array $array Variables will be stored in this array.
 * @since 2.2.1
 *
 */
function wp_parse_str( $string, &$array )
{
    parse_str( $string, $array );

    /**
     * Filters the array of variables derived from a parsed string.
     *
     * @param array $array The array populated with variables.
     * @since 2.3.0
     *
     */
    $array = apply_filters( 'wp_parse_str', $array );
}

/**
 * Safely extracts not more than the first $count characters from html string.
 *
 * UTF-8, tags and entities safe prefix extraction. Entities inside will *NOT*
 * be counted as one character. For example &amp; will be counted as 4, &lt; as
 * 3, etc.
 *
 * @param string $str String to get the excerpt from.
 * @param int $count Maximum number of characters to take.
 * @param string $more Optional. What to append if $str needs to be trimmed. Defaults to empty string.
 * @return string The excerpt.
 * @since 2.5.0
 *
 */
function wp_html_excerpt( $str, $count, $more = null )
{
    if ( null === $more ) {
        $more = '';
    }

    $str = wp_strip_all_tags( $str, true );
    $excerpt = mb_substr( $str, 0, $count );

    // Remove part of an entity at the end.
    $excerpt = preg_replace( '/&[^;\s]{0,6}$/', '', $excerpt );
    if ( $str != $excerpt ) {
        $excerpt = trim( $excerpt ) . $more;
    }

    return $excerpt;
}

/**
 * Properly strip all HTML tags including script and style
 *
 * This differs from strip_tags() because it removes the contents of
 * the `<script>` and `<style>` tags. E.g. `strip_tags( '<script>something</script>' )`
 * will return 'something'. wp_strip_all_tags will return ''
 *
 * @param string $string String containing HTML tags
 * @param bool $remove_breaks Optional. Whether to remove left over line breaks and white space chars
 * @return string The processed string.
 * @since 2.9.0
 *
 */
function wp_strip_all_tags( $string, $remove_breaks = false )
{
    $string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
    $string = strip_tags( $string );

    if ( $remove_breaks ) {
        $string = preg_replace( '/[\r\n\t ]+/', ' ', $string );
    }

    return trim( $string );
}

/**
 * Sanitizes a string from user input or from the database.
 *
 * - Checks for invalid UTF-8,
 * - Converts single `<` characters to entities
 * - Strips all tags
 * - Removes line breaks, tabs, and extra whitespace
 * - Strips octets
 *
 * @param string $str String to sanitize.
 * @return string Sanitized string.
 * @see wp_check_invalid_utf8()
 * @see wp_strip_all_tags()
 *
 * @since 2.9.0
 *
 * @see sanitize_textarea_field()
 */
function sanitize_text_field( $str )
{
    $filtered = _sanitize_text_fields( $str, false );

    /**
     * Filters a sanitized text field string.
     *
     * @param string $filtered The sanitized string.
     * @param string $str The string prior to being sanitized.
     * @since 2.9.0
     *
     */
    return apply_filters( 'sanitize_text_field', $filtered, $str );
}

/**
 * Sanitizes a multiline string from user input or from the database.
 *
 * The function is like sanitize_text_field(), but preserves
 * new lines (\n) and other whitespace, which are legitimate
 * input in textarea elements.
 *
 * @param string $str String to sanitize.
 * @return string Sanitized string.
 * @see sanitize_text_field()
 *
 * @since 4.7.0
 *
 */
function sanitize_textarea_field( $str )
{
    $filtered = _sanitize_text_fields( $str, true );

    /**
     * Filters a sanitized textarea field string.
     *
     * @param string $filtered The sanitized string.
     * @param string $str The string prior to being sanitized.
     * @since 4.7.0
     *
     */
    return apply_filters( 'sanitize_textarea_field', $filtered, $str );
}

/**
 * Internal helper function to sanitize a string from user input or from the db
 *
 * @param string $str String to sanitize.
 * @param bool $keep_newlines optional Whether to keep newlines. Default: false.
 * @return string Sanitized string.
 * @since 4.7.0
 * @access private
 *
 */
function _sanitize_text_fields( $str, $keep_newlines = false )
{
    if ( is_object( $str ) || is_array( $str ) ) {
        return '';
    }

    $str = (string)$str;

    $filtered = wp_check_invalid_utf8( $str );

    if ( strpos( $filtered, '<' ) !== false ) {
        $filtered = wp_pre_kses_less_than( $filtered );
        // This will strip extra whitespace for us.
        $filtered = wp_strip_all_tags( $filtered, false );

        // Use HTML entities in a special case to make sure no later
        // newline stripping stage could lead to a functional tag.
        $filtered = str_replace( "<\n", "&lt;\n", $filtered );
    }

    if ( !$keep_newlines ) {
        $filtered = preg_replace( '/[\r\n\t ]+/', ' ', $filtered );
    }
    $filtered = trim( $filtered );

    $found = false;
    while ( preg_match( '/%[a-f0-9]{2}/i', $filtered, $match ) ) {
        $filtered = str_replace( $match[ 0 ], '', $filtered );
        $found = true;
    }

    if ( $found ) {
        // Strip out the whitespace that may now exist after removing the octets.
        $filtered = trim( preg_replace( '/ +/', ' ', $filtered ) );
    }

    return $filtered;
}

/**
 * Convert lone less than signs.
 *
 * KSES already converts lone greater than signs.
 *
 * @param string $text Text to be converted.
 * @return string Converted text.
 * @since 2.3.0
 *
 */
function wp_pre_kses_less_than( $text )
{
    return preg_replace_callback( '%<[^>]*?((?=<)|>|$)%', 'wp_pre_kses_less_than_callback', $text );
}

/**
 * Callback function used by preg_replace.
 *
 * @param array $matches Populated by matches to preg_replace.
 * @return string The text returned after esc_html if needed.
 * @since 2.3.0
 *
 */
function wp_pre_kses_less_than_callback( $matches )
{
    if ( false === strpos( $matches[ 0 ], '>' ) ) {
        return esc_html( $matches[ 0 ] );
    }
    return $matches[ 0 ];
}

/**
 * Shorten a URL, to be used as link text.
 *
 * @param string $url URL to shorten.
 * @param int $length Optional. Maximum length of the shortened URL. Default 35 characters.
 * @return string Shortened URL.
 * @since 1.2.0
 *
 */
function url_shorten( $url, $length = 35 )
{
    $stripped = str_replace( [ 'https://', 'http://', 'www.' ], '', $url );
    $short_url = untrailingslashit( $stripped );

    if ( strlen( $short_url ) > $length ) {
        $short_url = substr( $short_url, 0, $length - 3 ) . '&hellip;';
    }
    return $short_url;
}

function esc_attr_e( $text )
{
    echo esc_attr( $text );
}

function esc_html_e( $text )
{
    echo esc_html( $text );
}

/**
 * Prints an escaped string
 * @param string $string
 * @param array $allowed_html
 * @param array $allowed_protocols
 */
function wp_kses_e( $string, $allowed_html, $allowed_protocols = [] )
{
    echo wp_kses( $string, $allowed_html, $allowed_protocols );
}

/**
 * Test if the current browser runs on a mobile device (smart phone, tablet, etc.)
 *
 * @return bool
 * @since 3.4.0
 *
 */
function wp_is_mobile()
{
    $is_mobile = false;

    if ( !empty( $_SERVER[ 'HTTP_USER_AGENT' ] ) ) {
        if ( strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Mobile' ) !== false // Many mobile devices (all iPhone, iPad, etc.)
            || strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Android' ) !== false
            || strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Silk/' ) !== false
            || strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Kindle' ) !== false
            || strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'BlackBerry' ) !== false
            || strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Opera Mini' ) !== false
            || strpos( $_SERVER[ 'HTTP_USER_AGENT' ], 'Opera Mobi' ) !== false ) {
            $is_mobile = true;
        }
    }

    /**
     * Filters whether the request should be treated as coming from a mobile device or not.
     *
     * @param bool $is_mobile Whether the request is from a mobile device or not.
     * @since 4.9.0
     *
     */
    return apply_filters( 'valpress/wp_is_mobile', $is_mobile );
}

/**
 * Replaces double line breaks with paragraph elements.
 *
 * A group of regex replaces used to identify text formatted with newlines and
 * replace double line breaks with HTML paragraph tags. The remaining line breaks
 * after conversion become <<br />> tags, unless $br is set to '0' or 'false'.
 *
 * @param string $pee The text which has to be formatted.
 * @param bool $br Optional. If set, this will convert all remaining line breaks
 *                    after paragraphing. Line breaks within `<script>`, `<style>`,
 *                    and `<svg>` tags are not affected. Default true.
 * @return string Text which has been converted into correct paragraph tags.
 * @since 0.71
 *
 */
function wpautop( $pee, $br = true )
{
    $pre_tags = [];

    if ( trim( $pee ) === '' ) {
        return '';
    }

    // Just to make things a little easier, pad the end.
    $pee = $pee . "\n";

    /*
     * Pre tags shouldn't be touched by autop.
     * Replace pre tags with placeholders and bring them back after autop.
     */
    if ( strpos( $pee, '<pre' ) !== false ) {
        $pee_parts = explode( '</pre>', $pee );
        $last_pee = array_pop( $pee_parts );
        $pee = '';
        $i = 0;

        foreach ( $pee_parts as $pee_part ) {
            $start = strpos( $pee_part, '<pre' );

            // Malformed HTML?
            if ( false === $start ) {
                $pee .= $pee_part;
                continue;
            }

            $name = "<pre wp-pre-tag-$i></pre>";
            $pre_tags[ $name ] = substr( $pee_part, $start ) . '</pre>';

            $pee .= substr( $pee_part, 0, $start ) . $name;
            $i++;
        }

        $pee .= $last_pee;
    }
    // Change multiple <br>'s into two line breaks, which will turn into paragraphs.
    $pee = preg_replace( '|<br\s*/?>\s*<br\s*/?>|', "\n\n", $pee );

    $allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';

    // Add a double line break above block-level opening tags.
    $pee = preg_replace( '!(<' . $allblocks . '[\s/>])!', "\n\n$1", $pee );

    // Add a double line break below block-level closing tags.
    $pee = preg_replace( '!(</' . $allblocks . '>)!', "$1\n\n", $pee );

    // Add a double line break after hr tags, which are self closing.
    $pee = preg_replace( '!(<hr\s*?/?>)!', "$1\n\n", $pee );

    // Standardize newline characters to "\n".
    $pee = str_replace( [ "\r\n", "\r" ], "\n", $pee );

    // Find newlines in all elements and add placeholders.
    $pee = wp_replace_in_html_tags( $pee, [ "\n" => ' <!-- wpnl --> ' ] );

    // Collapse line breaks before and after <option> elements so they don't get autop'd.
    if ( strpos( $pee, '<option' ) !== false ) {
        $pee = preg_replace( '|\s*<option|', '<option', $pee );
        $pee = preg_replace( '|</option>\s*|', '</option>', $pee );
    }

    /*
     * Collapse line breaks inside <object> elements, before <param> and <embed> elements
     * so they don't get autop'd.
     */
    if ( strpos( $pee, '</object>' ) !== false ) {
        $pee = preg_replace( '|(<object[^>]*>)\s*|', '$1', $pee );
        $pee = preg_replace( '|\s*</object>|', '</object>', $pee );
        $pee = preg_replace( '%\s*(</?(?:param|embed)[^>]*>)\s*%', '$1', $pee );
    }

    /*
     * Collapse line breaks inside <audio> and <video> elements,
     * before and after <source> and <track> elements.
     */
    if ( strpos( $pee, '<source' ) !== false || strpos( $pee, '<track' ) !== false ) {
        $pee = preg_replace( '%([<\[](?:audio|video)[^>\]]*[>\]])\s*%', '$1', $pee );
        $pee = preg_replace( '%\s*([<\[]/(?:audio|video)[>\]])%', '$1', $pee );
        $pee = preg_replace( '%\s*(<(?:source|track)[^>]*>)\s*%', '$1', $pee );
    }

    // Collapse line breaks before and after <figcaption> elements.
    if ( strpos( $pee, '<figcaption' ) !== false ) {
        $pee = preg_replace( '|\s*(<figcaption[^>]*>)|', '$1', $pee );
        $pee = preg_replace( '|</figcaption>\s*|', '</figcaption>', $pee );
    }

    // Remove more than two contiguous line breaks.
    $pee = preg_replace( "/\n\n+/", "\n\n", $pee );

    // Split up the contents into an array of strings, separated by double line breaks.
    $pees = preg_split( '/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY );

    // Reset $pee prior to rebuilding.
    $pee = '';

    // Rebuild the content as a string, wrapping every bit with a <p>.
    foreach ( $pees as $tinkle ) {
        $pee .= '<p>' . trim( $tinkle, "\n" ) . "</p>\n";
    }

    // Under certain strange conditions it could create a P of entirely whitespace.
    $pee = preg_replace( '|<p>\s*</p>|', '', $pee );

    // Add a closing <p> inside <div>, <address>, or <form> tag if missing.
    $pee = preg_replace( '!<p>([^<]+)</(div|address|form)>!', '<p>$1</p></$2>', $pee );

    // If an opening or closing block element tag is wrapped in a <p>, unwrap it.
    $pee = preg_replace( '!<p>\s*(</?' . $allblocks . '[^>]*>)\s*</p>!', '$1', $pee );

    // In some cases <li> may get wrapped in <p>, fix them.
    $pee = preg_replace( '|<p>(<li.+?)</p>|', '$1', $pee );

    // If a <blockquote> is wrapped with a <p>, move it inside the <blockquote>.
    $pee = preg_replace( '|<p><blockquote([^>]*)>|i', '<blockquote$1><p>', $pee );
    $pee = str_replace( '</blockquote></p>', '</p></blockquote>', $pee );

    // If an opening or closing block element tag is preceded by an opening <p> tag, remove it.
    $pee = preg_replace( '!<p>\s*(</?' . $allblocks . '[^>]*>)!', '$1', $pee );

    // If an opening or closing block element tag is followed by a closing <p> tag, remove it.
    $pee = preg_replace( '!(</?' . $allblocks . '[^>]*>)\s*</p>!', '$1', $pee );

    // Optionally insert line breaks.
    if ( $br ) {
        // Replace newlines that shouldn't be touched with a placeholder.
        $pee = preg_replace_callback( '/<(script|style|svg).*?<\/\\1>/s', '_autop_newline_preservation_helper', $pee );

        // Normalize <br>
        $pee = str_replace( [ '<br>', '<br/>' ], '<br />', $pee );

        // Replace any new line characters that aren't preceded by a <br /> with a <br />.
        $pee = preg_replace( '|(?<!<br />)\s*\n|', "<br />\n", $pee );

        // Replace newline placeholders with newlines.
        $pee = str_replace( '<WPPreserveNewline />', "\n", $pee );
    }

    // If a <br /> tag is after an opening or closing block tag, remove it.
    $pee = preg_replace( '!(</?' . $allblocks . '[^>]*>)\s*<br />!', '$1', $pee );

    // If a <br /> tag is before a subset of opening or closing block tags, remove it.
    $pee = preg_replace( '!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee );
    $pee = preg_replace( "|\n</p>$|", '</p>', $pee );

    // Replace placeholder <pre> tags with their original content.
    if ( !empty( $pre_tags ) ) {
        $pee = str_replace( array_keys( $pre_tags ), array_values( $pre_tags ), $pee );
    }

    // Restore newlines in all elements.
    if ( false !== strpos( $pee, '<!-- wpnl -->' ) ) {
        $pee = str_replace( [ ' <!-- wpnl --> ', '<!-- wpnl -->' ], "\n", $pee );
    }

    return $pee;
}

/**
 * Replace characters or phrases within HTML elements only.
 *
 * @param string $haystack The text which has to be formatted.
 * @param array $replace_pairs In the form array('from' => 'to', ...).
 * @return string The formatted text.
 * @since 4.2.3
 *
 */
function wp_replace_in_html_tags( $haystack, $replace_pairs )
{
    // Find all elements.
    $textarr = wp_html_split( $haystack );
    $changed = false;

    // Optimize when searching for one item.
    if ( 1 === count( $replace_pairs ) ) {
        // Extract $needle and $replace.
        foreach ( $replace_pairs as $needle => $replace ) {
            //...
        }

        // Loop through delimiters (elements) only.
        for ( $i = 1, $c = count( $textarr ); $i < $c; $i += 2 ) {
            if ( false !== strpos( $textarr[ $i ], $needle ) ) {
                $textarr[ $i ] = str_replace( $needle, $replace, $textarr[ $i ] );
                $changed = true;
            }
        }
    }
    else {
        // Extract all $needles.
        $needles = array_keys( $replace_pairs );

        // Loop through delimiters (elements) only.
        for ( $i = 1, $c = count( $textarr ); $i < $c; $i += 2 ) {
            foreach ( $needles as $needle ) {
                if ( false !== strpos( $textarr[ $i ], $needle ) ) {
                    $textarr[ $i ] = strtr( $textarr[ $i ], $replace_pairs );
                    $changed = true;
                    // After one strtr() break out of the foreach loop and look at next element.
                    break;
                }
            }
        }
    }

    if ( $changed ) {
        $haystack = implode( $textarr );
    }

    return $haystack;
}

/**
 * Separate HTML elements and comments from the text.
 *
 * @param string $input The text which has to be formatted.
 * @return string[] Array of the formatted text.
 * @since 4.2.4
 *
 */
function wp_html_split( $input )
{
    return preg_split( get_html_split_regex(), $input, -1, PREG_SPLIT_DELIM_CAPTURE );
}

/**
 * Retrieve the regular expression for an HTML element.
 *
 * @return string The regular expression
 * @since 4.4.0
 *
 */
function get_html_split_regex()
{
    static $regex;

    if ( !isset( $regex ) ) {
        // phpcs:disable Squiz.Strings.ConcatenationSpacing.PaddingFound -- don't remove regex indentation
        $comments =
            '!'             // Start of comment, after the <.
            . '(?:'         // Unroll the loop: Consume everything until --> is found.
            . '-(?!->)' // Dash not followed by end of comment.
            . '[^\-]*+' // Consume non-dashes.
            . ')*+'         // Loop possessively.
            . '(?:-->)?';   // End of comment. If not found, match all input.

        $cdata =
            '!\[CDATA\['    // Start of comment, after the <.
            . '[^\]]*+'     // Consume non-].
            . '(?:'         // Unroll the loop: Consume everything until ]]> is found.
            . '](?!]>)' // One ] not followed by end of comment.
            . '[^\]]*+' // Consume non-].
            . ')*+'         // Loop possessively.
            . '(?:]]>)?';   // End of comment. If not found, match all input.

        $escaped =
            '(?='             // Is the element escaped?
            . '!--'
            . '|'
            . '!\[CDATA\['
            . ')'
            . '(?(?=!-)'      // If yes, which type?
            . $comments
            . '|'
            . $cdata
            . ')';

        $regex =
            '/('                // Capture the entire match.
            . '<'           // Find start of element.
            . '(?'          // Conditional expression follows.
            . $escaped  // Find end of escaped element.
            . '|'           // ...else...
            . '[^>]*>?' // Find end of normal element.
            . ')'
            . ')/';
        // phpcs:enable
    }

    return $regex;
}

/**
 * Newline preservation help function for wpautop
 *
 * @param array $matches preg_replace_callback matches array
 * @return string
 * @since 3.1.0
 * @access private
 *
 */
function _autop_newline_preservation_helper( $matches )
{
    return str_replace( "\n", '<WPPreserveNewline />', $matches[ 0 ] );
}
