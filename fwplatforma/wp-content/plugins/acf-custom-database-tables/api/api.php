<?php
/**
 * General API function used by ACF Custom Database Tables. These are the functions that are used to interact with the
 * plugin and can be safely used by other plugins and/or themes.
 */

namespace ACFCustomDatabaseTables;

/**
 * Check for ACF version less than that given.
 *
 * @return bool
 */
function acf_version_lt( $version ) {
	return version_compare( ACF_VERSION, $version, '<' );
}