<?php

class ricUtil {

	// Force script enqueue
	static function do_scripts( $handles ) {
		global $wp_scripts;

		if ( ! is_a( $wp_scripts, 'WP_Scripts' ) )
			$wp_scripts = new WP_Scripts();

		$wp_scripts->do_items( ( array ) $handles );
	}

	// Enqueue a script
	static function js( $name, $footer = false, $deps = array() ) {
		$last = array_pop( explode( '/', $name ) );
		$slug = RIC . str_replace( '.', '-', $last );
		wp_enqueue_script( $slug, PT_URL . $name, $deps, '', $footer );
	}

	// Our jQuery
	static function jquery() {
		if ( ! is_admin() ) {
				wp_deregister_script( 'jquery' );
				wp_register_script( 'jquery', ( PT_URL . 'js/libs/jquery-1.7.1.min.js' ), false, '1.7.1', true );
				wp_enqueue_script( 'jquery' );
		}
	}

	// Add a style
	function css( $name, $media='all' ) {
		$last = array_pop( explode( '/', $name ) );
		$slug = RIC . str_replace( '.', '-', $last );
		wp_enqueue_style( $slug, PT_URL . $name, $deps, '', $media );
	}

	// Apply a function to each element of a ( nested ) array recursively
	static function array_map_recursive( $callback, $array ) {
		array_walk_recursive( $array, array( __CLASS__, 'array_map_recursive_helper' ), $callback );

		return $array;
	}

	static function array_map_recursive_helper( &$val, $key, $callback ) {
		$val = call_user_func( $callback, $val );
	}

	// Extract certain $keys from $array
	static function array_extract( $array, $keys ) {
		$r = array();

		foreach ( $keys as $key )
			if ( array_key_exists( $key, $array ) )
				$r[$key] = $array[$key];

		return $r;
	}

	// Extract a certain value from a list of arrays
	static function array_pluck( $array, $key ) {
		$r = array();

		foreach ( $array as $value ) {
			if ( is_object( $value ) )
				$value = get_object_vars( $value );
			if ( array_key_exists( $key, $value ) )
				$r[] = $value[$key];
		}

		return $r;
	}

	// Transform a list of objects into an associative array
	static function objects_to_assoc( $objects, $key, $value ) {
		$r = array();

		foreach ( $objects as $obj )
			$r[$obj->$key] = $obj->$value;

		return $r;
	}

	// Prepare an array for an IN statement
	static function array_to_sql( $values ) {
		foreach ( $values as &$val )
			$val = "'" . esc_sql( trim( $val ) ) . "'";

		return implode( ',', $values );
	}

	// Example: split_at( '</', '<a></a>' ) => array( '<a>', '</a>' )
	static function split_at( $delim, $str ) {
		$i = strpos( $str, $delim );

		if ( false === $i )
			return false;

		$start = substr( $str, 0, $i );
		$finish = substr( $str, $i );

		return array( $start, $finish );
	}

	// Add a trailing slash
	static function slash( $str ) {
		if( function_exists( 'trailingslashit' ) ) {
			return trailingslashit( $str );
		}

		if( substr($str, -1) != '/' ) $str .= '/';
		return $str;
	}

	// Return or display an option
	static function opt( $option, $echo = true ) {
		$str = get_option( RIC . $option );

		if( $echo ) {
			$str = htmlspecialchars( stripslashes( $str ) );
			echo $str;
		}

		return $str;
	}

	// Return or display a meta value
	static function meta( $post_id, $key, $echo = true ) {
		$str = get_post_meta( $post_id, $key, true );

		if( $echo ) {
			$str = htmlspecialchars( stripslashes( $str ) );
			echo $str;
		}

		return $str;
	}

	// Filtered output for MCE
	static function meta_mce( $post_id, $key, $echo = true ) {
		$str = self::meta( $post_id, $key, false );
		$str = self::filter_mce( $str );

		if( $echo ) echo $str;

		return $str;
	}

	static function opt_mce( $key, $echo = true ) {
		$str = self::opt( $key, false );
		$str = self::filter_mce( $str );

		if( $echo ) echo $str;

		return $str;
	}

	static function filter_mce( $content ) {
		return apply_filters( 'the_content', $content );
	}

	// Checks for valid URL
	static function is_url( $url ) {
		return ( ( ! empty( $url ) ) and ( $url == esc_url( $url ) ) );
	}

	// Current request URL
	static function current_url() {
		$url = ( 'on' == $_SERVER['HTTPS'] ) ? 'https://' : 'http://';
		$url .= $_SERVER['SERVER_NAME'];
		$url .= ( '80' == $_SERVER['SERVER_PORT'] ) ? '' : ':' . $_SERVER['SERVER_PORT'];
		$url .= $_SERVER['REQUEST_URI'];

		return self::slash( $url );
	}

	// Make a slug (only dashes -)
	static function slugify( $text ) {
		return sanitize_title( $text );
	}

	// Get the ordinal of a number
	static function make_ordinal( $num ) {
		if ( ! in_array( ( $num % 100 ), array( 11, 12, 13 ) ) ) {
			switch ( $num % 10 ) {
				// Handle 1st, 2nd, 3rd
				case 1: return $num . 'st';
				case 2: return $num . 'nd';
				case 3: return $num . 'rd';
			}
		}

		return $num . 'th';
	}

	// Get attachment ID from URL
	static function attach_id ( $url ) {
		if( ! self::is_url( $url ) )
			return false;

		global $wpdb;

		$query = "SELECT {$wpdb->posts}.ID  FROM {$wpdb->posts} WHERE {$wpdb->posts}.post_type = 'attachment' AND {$wpdb->posts}.guid = '{$url}'";

		if($row = $wpdb->get_row($query)) {
			return $row->ID;
		}

		return false;
	}

	/**
	 * Returns an array containing every
	 * non-empty line of a textarea
	 *
	 * @param $str - the textarea contents
	 */
	static function process_textarea( $str ) {
		// Split at newline
		$parts = preg_split( '/(\r\n|\n|\r)/', $str );

		$ret = array();

		foreach( $parts as $part ) {
			$part = esc_attr(trim($part));
			if( ! empty( $part ) ) {
				$ret[] = $part;
			}
		}

		return $ret;
	}

}


//_____Debug_____

if( ! function_exists( 'd' ) ):
function d( $data ) {
	/*if ( class_exists( 'FirePHP' ) ) {
		$firephp = FirePHP::getInstance( true );
		$firephp->group( 'debug' );
		$firephp->log( $data );
		$firephp->groupEnd();
		return;
	}*/

	if( is_array( $data ) ) {
		print "<pre>-----------------------\n";
		print_r( $data );
		print "-----------------------</pre>";
	} elseif ( is_object( $data ) ) {
		print "<pre>==========================\n";
		var_dump( $data );
		print "===========================</pre>";
	} else {
		print "=========&gt; ";
		var_dump( $data );
		print " &lt;=========";
	}
}
endif;

if( ! function_exists( 'dpb' ) ):
function dpb() {
	echo '<pre>';
	debug_print_backtrace();
	echo '</pre>';
}
endif;


//_____Minimalist HTML framework_____

if ( ! function_exists( 'html' ) ):
function html( $tag, $attributes = array(), $content = '' ) {
	if ( is_array( $attributes ) ) {
		$closing = $tag;
		foreach ( $attributes as $key => $value ) {
			if( ! empty( $value ) or $value == '0' ) {
				$tag .= ' ' . $key . '="' . esc_attr( $value ) . '"';
			}
		}
	} else {
		$content = $attributes;
		list( $closing ) = explode(' ', $tag, 2);
	}

	return "<{$tag}>{$content}</{$closing}>";
}
endif;


if ( ! function_exists( 'html_link' ) ):
function html_link( $url, $title = '' ) {
	if ( empty( $title ) )
		$title = $url;

	return sprintf( "<a href='%s' title='%s'>%s</a>", esc_url( $url ), $title, $title );
}
endif;

if ( ! function_exists( 'html_img' ) ):
function html_img( $url, $attributes = array(), $echo = true ) {
	if ( ! ricUtil::is_url( $url ) ) return false;

	if ( ! isset( $attributes['alt'] ) ) $attributes['alt'] = ''; // This attribute is required

	$gis = getimagesize( $url );

	if( $gis ) {
		$tag = 'img';

		$attributes['src'] = $url;

		if( ! ( isset( $attributes['width'] ) or isset( $attributes['height'] ) ) ){
			list( $attributes['width'], $attributes['height'] ) = $gis;
		}

		foreach( $attributes as $key => $value ) {
			$tag .= ' ' . $key . '="' . esc_attr( $value ) . '"';
		}

		$ret = "<{$tag}/>";

		if( $echo ) echo $ret;

		return $ret;
	}
}
endif;

if ( ! function_exists( 'attach_img' ) ):
function attach_img( $args ) {
	$defaults = array(
		'post_id' => false,
		'attach_id' => false,
		'url' => false,
		'meta_slug' => false,
		'option_slug' => false,
		'size' => 'full',
		'echo' => true
	);

	extract( wp_parse_args( $args, $defaults ) );

	if( ! $url and ! $post_id ) {
		global $post;
		$post_id = $post->ID;
	}

	// Try the meta slug first
	if( ! $url and $meta_slug ) {
		$url = ricUtil::meta( $post_id, $meta_slug, false );
	}

	// Then the option slug
	if( ! $url and $option_slug ) {
		$url = ricUtil::opt( $option_slug, false );
	}

	if ( $url ) {
		$attach_id = ricUtil::attach_id( $url );
	}

	if( $attach_id ) {
		$img = wp_get_attachment_image( $attach_id, $size, false );
		if( $echo ) echo $img;
		return $img;
	}

	return false;
}
endif;

if( ! function_exists( 'opt' ) ):
	function opt( $name, $echo = false ) {
		return ricUtil::opt( $name, $echo );
	}
endif;

