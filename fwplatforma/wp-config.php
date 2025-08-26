<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'fwplatforma' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '$k;mJhcMIQp5yYD6}lX2;+|Rx:[v2-Z^gK<P.~`w;zssgzZ[I8q_!Y2i+XK&f?qp' );
define( 'SECURE_AUTH_KEY',  'p<Okb(AMg[PFfCIecCl-0%VipWOq#JirSW+!pw_Gc2$O=Jg0-P#W4R75Ja!igtMi' );
define( 'LOGGED_IN_KEY',    ';;B542Y%fumEF<&,(7#:`UAsy2DUJ* P#f__]2Qxxv@Mw#l:1vXKx}`;+YGh>9zN' );
define( 'NONCE_KEY',        'nLWg8D&FjKZwUZ+=^44g#<(yeuwK@kNssZNX2*L]}TG&9OmEg)e2/6E%tkP7SF*U' );
define( 'AUTH_SALT',        '^4~k1|Kd=fVbKoMuqX~I$&tTqt6TQ{1Q~}_>65vZU%8IeSzV/b#}jkhuRcM@..OO' );
define( 'SECURE_AUTH_SALT', '@3~GW[)?{u!*Kf0;Xei_GW_7iaY32}UW|Y^ceW8[c>F7^xV`tNS0NW$[G*zi7V31' );
define( 'LOGGED_IN_SALT',   '[8MUkO4j6uO2p:Fz7CeMY^O{-p_x7~))7.*)uNorVaKPK/0@f2>pk_ yVNNNNE%{' );
define( 'NONCE_SALT',       '(4X9=J$|/Ktb&vJPg3TrIdk?FYONnJLH)XKz)0(7QTp_wGe|nw<:=<)#108YX>I~' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

/*define('JWT_AUTH_SECRET_KEY', 'xRW3NdDDWzgmG7jp66XOyptOfe3dS8KgFn2w60tW5E3kQla9BF'); // Придумай сильный ключ, например, случайную строку из 50 символов.
define('JWT_AUTH_CORS_ENABLE', true);*/