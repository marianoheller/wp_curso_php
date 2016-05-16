<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'wordpress');

/** MySQL database password */
define('DB_PASSWORD', 'zxcvbnmqp1');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org 
secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to 
have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'Q3|;^<pZ}R=Jd0A)MWi=XS8IC:aKBW0dYt5|W b(;ar-Wu#=bsVe~Ttrzjgc!&Yz');
define('SECURE_AUTH_KEY',  'IK&BGTbGzf%%HthNt:;K<|Fwt`Cl&|YZv$oV>XM(rHz3.L&oiDU RgInn0t0Ym4!');
define('LOGGED_IN_KEY',    ',R$mrq>oVgtd[Z17)3Uu0-)@!59l}ES#HADwJ%F;B9!`7t[DWIekny+%OIawM9+H');
define('NONCE_KEY',        '7EI9/},y>XnnG)LIty/Wx<0R>d4l7]8<e)+8kCrckw?vxV_a2EcUn_.$!c-~fi*9');
define('AUTH_SALT',        '1N>U%VTcDz<U/2r#G:S$-9#e^Z} |#`8wc@JN W~!;7cLFJqK6[U?,Vh$~-e$(y+');
define('SECURE_AUTH_SALT', ',bnF 6wwM3?(G<:5RNmxK#-RaN|$>S*lpT#j<D:0Vg46J|!SnT{?ys7S87XPJ%rL');
define('LOGGED_IN_SALT',   'QB-GD.^DiA2>{~IOCf0HNpFuVz .$D:Asl4zJ?-VvAyI{#/.5.x#QF|pP:?}Y/jt');
define('NONCE_SALT',       '4!EotT|K-=cgD#eNyZId:2f&LjPk6PM[<Te/v~38$Z`Af0e*g99rH]}Hs$=X:CX8');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');



/** FTP credentials */
/** define( 'FTP_USER', 'mariano' ); */
/** define( 'FTP_PASS', 'dostoievski' ); */
define( 'FTP_HOST', 'localhost' );

/** Sets up direct method, withour ftp! */
define ( 'FS_METHOD', 'direct');
