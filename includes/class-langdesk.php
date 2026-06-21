<?php
defined( 'ABSPATH' ) || exit;

require_once LANGDESK_DIR . 'includes/class-langdesk-polylang.php';
require_once LANGDESK_DIR . 'includes/class-langdesk-assignment.php';
require_once LANGDESK_DIR . 'includes/class-langdesk-guard.php';

/**
 * Bootstrap: dependency check + hook registration only. Business logic lives in
 * the component classes (Polylang bridge, Assignment, Guard, Workspace).
 */
final class LangDesk {

	private static ?self $instance = null;

	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Translations load automatically on WordPress.org (WP 4.6+); no
		// load_plugin_textdomain() call is needed.

		// Graceful degrade: with no Polylang there is no language to guard, so we
		// register nothing and only warn admins. Never fatal, never lock the site.
		if ( ! LangDesk_Polylang::is_active() ) {
			add_action( 'admin_notices', [ $this, 'polylang_missing_notice' ] );
			return;
		}

		$this->register();
	}

	/**
	 * Wire up the components. The Guard runs everywhere (front, admin, REST) so
	 * capability checks can never be bypassed. The Workspace/Assignment UI is
	 * admin-only.
	 */
	private function register(): void {
		( new LangDesk_Guard() )->register();

		if ( is_admin() ) {
			require_once LANGDESK_DIR . 'includes/class-langdesk-assignment.php';
			require_once LANGDESK_DIR . 'includes/class-langdesk-workspace.php';

			( new LangDesk_Assignment() )->register();
			( new LangDesk_Workspace() )->register();
		}
	}

	public function polylang_missing_notice(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		echo '<div class="notice notice-warning"><p>';
		echo esc_html__( 'LangDesk requires Polylang to be installed and active. Language restrictions are currently disabled.', 'langdesk-for-polylang' );
		echo '</p></div>';
	}
}
