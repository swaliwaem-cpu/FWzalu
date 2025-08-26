<?php

namespace ACFCustomDatabaseTables\Utils;

class Html {

	/**
	 * Print readonly attributes for HTML form inputs. In WordPress 5.9, the readyonly() fn is depreacted in favour of
	 * wp_readonly(). This method ensures we won't break for anyone using less than WP 5.9.
	 *
	 * @param $readonly
	 * @param bool $current
	 * @param bool $echo
	 *
	 * @return string
	 */
	public static function readonly( $readonly, $current = true, $echo = true ) {
		if ( function_exists( 'wp_readonly' ) ) {
			return wp_readonly( $readonly, $current, $echo );
		} elseif ( function_exists( 'readonly' ) ) {
			return readonly( $readonly, $current, $echo );
		}
		return '';
	}

}