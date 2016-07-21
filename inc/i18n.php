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

// Exit early in case multiple Composer autoloaders try to include this file.
if ( function_exists( __NAMESPACE__ . '\\' . 'load_translations' ) ) {
	return;
}

/**
 * @param string $path
 *
 * @return bool
 */
function load_translations( $path = '' ) {

	// If called outside WP context, let's cleanup WP globals and exit.
	if ( ! function_exists( 'apply_filters' ) ) {

		cleanup_globals();

		return;
	}

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
		$done[ 0 ] = load_textdomain( $domain, $path );

		return $done[ 0 ];
	}

	// Build .mo file name
	$file = $domain . '-' . apply_filters( 'plugin_locale', get_locale(), $domain ) . '.mo';

	// If user provided a valid path that contains the .mo file we are looking for, loads it. Otherwise, use default.
	$folder = ( $path && is_dir( $path ) && file_exists( trailingslashit( $path ) . $file ) )
		? $path
		: dirname( __DIR__ ) . '/languages';

	$done[ 0 ] = load_textdomain( $domain, trailingslashit( $folder ) . $file );

	return $done[ 0 ];
}

/**
 * If the package is used outside of WP context, we probably want to cleanup global vars
 * we used to setup WP hook to load translations.
 */
function cleanup_globals() {

	// If in WP context, don't mess up with global `$wp_filter`.
	// If global `$wp_filter` already empty, there's nothing to cleanup.
	if (
		function_exists( 'add_action' )
		|| ! is_array( $GLOBALS[ 'wp_filter' ] )
		|| empty( $GLOBALS[ 'wp_filter' ] )
	) {
		return;
	}

	$callable = __NAMESPACE__ . '\\load_translations';

	if ( isset( $GLOBALS[ 'wp_filter' ][ 'after_setup_theme' ][ 99 ][ $callable ] ) ) {
		unset( $GLOBALS[ 'wp_filter' ][ 'after_setup_theme' ][ 99 ][ $callable ] );
	}

	if (
		array_key_exists( 'after_setup_theme', $GLOBALS[ 'wp_filter' ] )
		&& array_key_exists( 99, $GLOBALS[ 'wp_filter' ][ 'after_setup_theme' ] )
		&& empty( $GLOBALS[ 'wp_filter' ][ 'after_setup_theme' ][ 99 ] )
	) {
		unset( $GLOBALS[ 'wp_filter' ][ 'after_setup_theme' ][ 99 ] );
	}

	if (
		array_key_exists( 'after_setup_theme', $GLOBALS[ 'wp_filter' ] )
		&& empty( $GLOBALS[ 'wp_filter' ][ 'after_setup_theme' ] )
	) {
		unset( $GLOBALS[ 'wp_filter' ][ 'after_setup_theme' ] );
	}

	if ( empty( $GLOBALS[ 'wp_filter' ] ) ) {
		unset( $GLOBALS[ 'wp_filter' ] );
	}
}

// This file is loaded by Composer autoload, and that may happen before `add_action` is available.
// In that case, we "manually" add in global `$wp_filter` the function that loads translations.
// We use `after_setup_theme` with late priority so that from a plugin or theme would be possible to remove the hook
// (and load no translation) or change the translation path via 'inpsyde-validator.translation_path' filter.
// If an user want to load translation before 'after_setup_theme' is fired, it is possible to call
// `load_translations()` directly.

if ( ! function_exists( 'add_action' ) ) {

	global $wp_filter;
	is_array( $wp_filter ) or $wp_filter = [ ];
	isset( $wp_filter[ 'after_setup_theme' ] ) or $wp_filter[ 'after_setup_theme' ] = [ ];
	isset( $wp_filter[ 'after_setup_theme' ][ 99 ] ) or $wp_filter[ 'after_setup_theme' ][ 99 ] = [ ];

	$wp_filter[ 'after_setup_theme' ][ 99 ][ __NAMESPACE__ . '\\load_translations' ] = [
		__NAMESPACE__ . '\\' . 'load_translations',
		1
	];

} else {

	add_action( 'after_setup_theme', __NAMESPACE__ . '\\load_translations', 99 );
}


