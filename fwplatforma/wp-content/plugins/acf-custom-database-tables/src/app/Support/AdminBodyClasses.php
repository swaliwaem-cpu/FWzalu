<?php

namespace ACFCustomDatabaseTables\Support;

use function ACFCustomDatabaseTables\acf_version_lt;

class AdminBodyClasses {

	private static $classes = [];

	public static function init( $classes = [] ) {
		self::$classes = $classes;
		add_filter( 'admin_body_class', [ __CLASS__, 'add_body_class' ] );
	}

	public static function add_body_class( $body_class ) {
		// Add a flag so we can target versions of ACF that are 6.0 and above.
		if ( ! acf_version_lt( 6 ) ) {
			$body_class .= ' acfcdt-acf-gte-6';
		}

		// Add any dynamic classes passed to the class on init.
		if ( self::$classes ) {
			$body_class .= ' ' . implode( ' ', self::$classes );
		}

		return $body_class;
	}

}