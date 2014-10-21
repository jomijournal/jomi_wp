<?php
/** Enable W3 Total Cache */
define('WP_CACHE', true); // Added by W3 Total Cache

/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

/** local vs remote detection */

// ONLY ENABLE THIS BLOCK IF YOU NEED TO MESS WITH SEARCHING + QUERIES
// OTHERWISE THIS MAKES LOCALHOST CRAZY SLOW

$base_path = substr(ABSPATH, strlen($_SERVER['DOCUMENT_ROOT']));
define('WP_SITEURL', "http://${_SERVER['HTTP_HOST']}${base_path}");
define('WP_HOME',    "http://${_SERVER['HTTP_HOST']}${base_path}");

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'jomi');

/** MySQL database username */
define('DB_USER', 'user');

/** MySQL database password */
define('DB_PASSWORD', 'pass');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');


/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'Dn%<wl^VvgK!-D+U[0~aS)Ew`Hxtt(?~y.Z|a|Y`JphE+9$P~k^&a8T6~VoC*xS%');
define('SECURE_AUTH_KEY',  'R@+HObe@~Ci2CZrziq]uV/Ez2U%/<),1D3FL|&`C]*-/Gq]snjQHt:[Zwnj=!q6Y');
define('LOGGED_IN_KEY',    '6oO_V0xfvu|kB_4YI|$1gjVha,1#_P+>zPw(^Saw#S$Zq+mBCKz0heQK)f-PI{;h');
define('NONCE_KEY',        '1XYEZkJ4-e4jr)_Dm3mmhB52C38;y_t&xj>q[+m):`|3&-Gr]+2EcNA@vLdGr6i5');
define('AUTH_SALT',        '.leEBhv-VM*G$zf:SK />+pC@S p{&~u^MD@h_QV|Yg;Fi$vX0S8z<.k*fYtJgoQ');
define('SECURE_AUTH_SALT', 'JXwD-! {`=I#&+wuU4Wq`E;/q0=c<B~aD-z.Wyc!8VvzF{fw?6@wdC,TKOV-F:Oh');
define('LOGGED_IN_SALT',   'PzHnrX-0!k&^z BbMAcNqb#5kUN&MA}/WSlW#:0QCkgkh!R)cuk+K%!qdcV`[}G7');
define('NONCE_SALT',       'ECi#-<7M8PhH3K9tM4KYCk@iw(t(Rs_*rLs66o`a`|Rmo0NRo$ C%47/q]TTM1N+');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
