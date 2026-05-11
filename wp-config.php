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
define( 'DB_NAME', 'organic_shop' );

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
define( 'AUTH_KEY',         'o/FwTma*AxG^sv_zBxD$-<Oel ,~PCXkTcF#w;lbY0#reH(ym`uNKq!{#ZQy@A(T' );
define( 'SECURE_AUTH_KEY',  '9YiV1j&3@%V:7anjU5(@88fh,<GJ}X9W61JFR@a7d!>goK,faB46IBOq^>%(j_To' );
define( 'LOGGED_IN_KEY',    'O3{gO@$6 wMdfz0]|V[1dn2hdxpsqXFw_*+*Mz3nh.PAdOBu`}5OBngWJhdh}N)a' );
define( 'NONCE_KEY',        'Zmi=N8<!0%`0U^9RM_FXN{IMY*_Dd,y;1H%+T![QGbNv0MC+7=sSWIAsciM?}jnr' );
define( 'AUTH_SALT',        'JszPER*@$;j(kL<9%fTmowK&9t!Uz?K)knn9)1FM^Y`pRWlYp;GCZ@N%I1(c`!n)' );
define( 'SECURE_AUTH_SALT', 'boM(rUhm;=<a.yya?RNGjBTF,~Yy2_zRDmoS-;wMAvN*CbvKd{<DO49n#a$73X|W' );
define( 'LOGGED_IN_SALT',   'tSRAujzJgf)T,?;-[h|s|FX8sQHd^S<,Aj{aDqKpROJwk>7k|<n$u~:Fz)mIB},k' );
define( 'NONCE_SALT',       'kCdXpktRfZ6QUYNP7c/nxV{6,RG.okG:wF4;`tL&oI7UMtHY@v5h%_nuVb)`:C0>' );

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
