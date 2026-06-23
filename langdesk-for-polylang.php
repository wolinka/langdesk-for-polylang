<?php
/**
 * Plugin Name:       LangDesk: Translation Roles for Polylang
 * Plugin URI:        https://github.com/wolinka/langdesk-for-polylang
 * Description:       Assign languages to editors so each translator works only in their own language, with a clean to-translate queue. Built for Polylang.
 * Version:           1.0.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Wolinka
 * Author URI:        https://wolinka.com.tr
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       langdesk-for-polylang
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'LANGDESK_VERSION', '1.0.1' );
define( 'LANGDESK_FILE', __FILE__ );
define( 'LANGDESK_DIR', plugin_dir_path( __FILE__ ) );
define( 'LANGDESK_URL', plugin_dir_url( __FILE__ ) );

/**
 * Boot on plugins_loaded @20: Polylang loads earlier, so its pll_* API is
 * available for the dependency check. The bootstrap decides whether to wire up
 * the language guards or degrade gracefully.
 */
add_action(
	'plugins_loaded',
	static function () {
		require_once LANGDESK_DIR . 'includes/class-langdesk.php';
		LangDesk::get_instance();
	},
	20
);
