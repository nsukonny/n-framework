<?php
/**
 * Load all classes from includes folder
 */

namespace NSukonny\NFramework;

function autoload( $class ) {
	echo $class . ' ----- <br>';
	global $nhg_autoload_namespaces;

	echo '<pre>' . print_r( $class, true ) . '</pre>';
	if ( strpos( $class, 'NHG\\' ) !== 0 || empty( $nhg_autoload_namespaces ) ) {
		return;
	}

	$load_path = null;
	$autoload  = false;

	$pieces    = explode( '\\', $class );
	$classname = array_pop( $pieces );
	$namespace = implode( '\\', $pieces );

	foreach ( $nhg_autoload_namespaces as $key => $load_path ) {
		if ( $namespace === $key || ( strpos( $namespace, $key . '\\' ) === 0 ) ) {
			$autoload = true;
			break;
		}
	}

	if ( ! $autoload || ! $load_path ) {
		return;
	}

	$path = $load_path . '/includes' . strtolower( str_replace( [ '\\', '_' ], [
			'/',
			'-'
		], substr( $namespace, strlen( $key ) ) ) ) . '/';
	$slug = strtolower( str_replace( '_', '-', $classname ) ) . '.php';

	$prefixes = [ 'class', 'trait', 'abstract' ];

	foreach ( $prefixes as $prefix ) {
		$filename = $path . $prefix . '-' . $slug;

		if ( file_exists( $filename ) ) {
			require_once $filename;

			return;
		}
	}
}

spl_autoload_register( __NAMESPACE__ . '::autoload' );