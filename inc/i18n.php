<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the inpsyde-validator package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Inpsyde\Validator;

/**
 * @param string $path
 *
 * @return bool
 */
function load_translations( $path = '' ) {

	// Prevent function is called more than once with same path as argument (which would mean load same file again)
	static $done;
	if ( is_array( $done ) && $path === end( $done ) ) {
		return reset( $done );
	}

	$done   = [ FALSE, $path ];
	$domain = 'inpsyde-validator';

	// Filter the .mo path
	$path = apply_filters( $domain . '.translation_path', $path );

	// If user provides a path to a .mo file, just loads it
	if ( is_file( $path ) && strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) === 'mo' ) {
		load_textdomain( 'inpsyde-validator', $path );
	}

	// Build .mo file name
	$file = $domain . '-' . apply_filters( 'plugin_locale', get_locale(), $domain ) . '.mo';

	// If user provided a valid path that contains the .mo file we are looking for, loads it. Otherwise, use default.
	$folder = ( $path && is_dir( $path ) && file_exists( trailingslashit( $path ) . $file ) )
		? $path
		: dirname( __DIR__ ) . '/languages';

	$done[ 0 ] = load_textdomain( 'inpsyde-validator', trailingslashit( $folder ) . $file );

	return $done[ 0 ];
}

// This file might be loaded from Composer autoload before `add_action` is available.
// In that case, we "manually" add the function that loads the translation in global `$wp_filter`.
// We use `after_setup_theme` with late priority so that from a plugin or theme would be possible to remove the hook
// (and load no translation) or change the translation path via 'inpsyde-validator.translation_path' filter.
// If an user want to load translation before 'after_setup_theme' is fired, it is possible to call
// `load_translations()` directly.

if ( ! function_exists( 'add_action' ) ) {

	global $wp_filter;
	is_array( $wp_filter ) or $wp_filter = [ ];
	isset( $wp_filter[ 'after_setup_theme' ] ) or $wp_filter[ 'after_setup_theme' ] = [ ];
	isset( $wp_filter[ 'after_setup_theme' ][ 99 ] ) or $wp_filter[ 'after_setup_theme' ][ 99 ] = [ ];

	$wp_filter[ 'after_setup_theme' ][ 99 ][ __NAMESPACE__ . '\\' . 'load_translations' ] = [
		__NAMESPACE__ . '\\' . 'load_translations',
		1
	];

} else {

	add_action( 'after_setup_theme', __NAMESPACE__ . '\\' . 'load_translations', 99 );
}


