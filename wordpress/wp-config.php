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
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

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
define( 'AUTH_KEY',         'ww} t1}{RMUN~= Va0aetbo S@s4a[Z4,5[{]4FNu s~UYGzwVXv>shm;>Z.3mzt' );
define( 'SECURE_AUTH_KEY',  '.81NPkT%EjlUI!y[yJK?gdqRyR:f]?I7)G#Au{=Oo%hzo{&N|5<|X7x<JcgP_x<B' );
define( 'LOGGED_IN_KEY',    'BqHHm/`9ia9h5As*!mpQ@a+OE`*OnYJw1Ei,t:~qyPNT_gU4;$gzK(f$,t>(~NCs' );
define( 'NONCE_KEY',        '#T~iC3Jxo.*YAe>a]c03}0M#O <rP_Jsd1c4IHH>KC+vm$&*K#YKAyVh9v316p-s' );
define( 'AUTH_SALT',        '7FTB}H}v4V9Jec$M1/]k*MtZRbI,:O=3PcuG1Xw;Qlhn@[9e;fDY&#}(OsNB|ZeI' );
define( 'SECURE_AUTH_SALT', 'HR/7@AbR>D.|?V73z1E,%K+%<&+J~x:+PDYAKNC75D*uEu*ilAdXykia{4e|<.3*' );
define( 'LOGGED_IN_SALT',   ';may{NI@fvG<:VGd46orvMN}tos~&WX}+Q;DWR$/)]=:i>Klps2:Kh*J8oo+qnHI' );
define( 'NONCE_SALT',       'e<Z6_8whE`&]aSk<G=(@QVb>A4,gG1Ro~&XG(SU.fXByiRAJzr*^J?-smn.!1hl_' );

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
