<?php
defined( 'ABSPATH' ) || exit;

/**
 * Assignment layer (data model): which language(s) a user may work in.
 *
 * Stored as a single user meta `langdesk_allowed_langs` holding an array of
 * Polylang language slugs. A user with no assigned languages is unrestricted
 * (a normal editor outside the translation workflow). A user is "restricted"
 * only when they have at least one assigned language and are not a site manager.
 */
class LangDesk_Assignment {

	public const META_KEY = 'langdesk_allowed_langs';

	public function register(): void {
		// Show the language assignment on user profile screens.
		add_action( 'show_user_profile', [ $this, 'render_fields' ] );
		add_action( 'edit_user_profile', [ $this, 'render_fields' ] );

		// Persist it (with nonce + capability + whitelist sanitisation).
		add_action( 'personal_options_update', [ $this, 'save_fields' ] );
		add_action( 'edit_user_profile_update', [ $this, 'save_fields' ] );
	}

	/**
	 * Languages a user is allowed to work in, intersected with the languages
	 * that actually exist in Polylang (so stale slugs are ignored).
	 *
	 * @return string[]
	 */
	public static function get_user_languages( int $user_id ): array {
		$stored = get_user_meta( $user_id, self::META_KEY, true );
		if ( ! is_array( $stored ) ) {
			return [];
		}
		$valid = LangDesk_Polylang::languages();
		return array_values( array_intersect( array_map( 'strval', $stored ), $valid ) );
	}

	/**
	 * A site manager is never restricted by LangDesk. We key off a standard,
	 * high-trust capability so the rule composes with existing roles instead of
	 * inventing a new one.
	 */
	public static function manages_all_languages( int $user_id ): bool {
		return user_can( $user_id, 'manage_options' );
	}

	/** Is this user scoped to specific languages by LangDesk? */
	public static function is_restricted( int $user_id ): bool {
		if ( self::manages_all_languages( $user_id ) ) {
			return false;
		}
		return ! empty( self::get_user_languages( $user_id ) );
	}

	/**
	 * Render the language checkboxes on a user's profile. Only users who can
	 * edit other users (managers) may assign languages.
	 */
	public function render_fields( WP_User $user ): void {
		// Only users who can manage OTHER users may assign languages. This must
		// NOT be 'edit_user' (every user can edit their own profile), otherwise a
		// restricted translator could grant themselves extra languages from their
		// own profile and defeat the whole restriction.
		if ( ! current_user_can( 'edit_users' ) ) {
			return;
		}

		$languages = LangDesk_Polylang::languages();
		if ( empty( $languages ) ) {
			return;
		}

		$assigned = self::get_user_languages( $user->ID );
		wp_nonce_field( 'langdesk_save_languages', 'langdesk_languages_nonce' );
		?>
		<h2><?php esc_html_e( 'LangDesk: Translation Languages', 'langdesk-for-polylang' ); ?></h2>
		<?php if ( self::manages_all_languages( $user->ID ) ) : ?>
			<p class="description" style="color:#b32d2e;">
				<?php esc_html_e( 'This user can manage the whole site, so LangDesk does not restrict them by language. To enforce a language, give them a role such as Editor, Author or Contributor instead of Administrator.', 'langdesk-for-polylang' ); ?>
			</p>
		<?php endif; ?>
		<table class="form-table" role="presentation">
			<tr>
				<th><?php esc_html_e( 'Allowed languages', 'langdesk-for-polylang' ); ?></th>
				<td>
					<fieldset>
						<?php foreach ( $languages as $slug ) : ?>
							<label style="display:block;margin-bottom:4px;">
								<input type="checkbox"
									name="langdesk_allowed_langs[]"
									value="<?php echo esc_attr( $slug ); ?>"
									<?php checked( in_array( $slug, $assigned, true ) ); ?> />
								<?php echo esc_html( LangDesk_Polylang::language_name( $slug ) ); ?>
								<code><?php echo esc_html( $slug ); ?></code>
							</label>
						<?php endforeach; ?>
						<p class="description">
							<?php esc_html_e( 'Leave empty for an unrestricted user. When at least one language is selected, this user can only edit content in those languages (site managers are never restricted).', 'langdesk-for-polylang' ); ?>
						</p>
					</fieldset>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save the assignment. Fail-closed on every gate: nonce, capability, and a
	 * strict whitelist against the languages Polylang actually defines.
	 */
	public function save_fields( int $user_id ): void {
		if ( ! isset( $_POST['langdesk_languages_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['langdesk_languages_nonce'] ) ), 'langdesk_save_languages' ) ) {
			return;
		}
		// Manager-only: must be able to edit OTHER users. A restricted translator
		// (who can edit their own profile) must never assign their own languages.
		if ( ! current_user_can( 'edit_users' ) ) {
			return;
		}

		$valid = LangDesk_Polylang::languages();

		// Sanitize at the point of access: each submitted slug through sanitize_key.
		$submitted = [];
		if ( isset( $_POST['langdesk_allowed_langs'] ) && is_array( $_POST['langdesk_allowed_langs'] ) ) {
			$submitted = array_map( 'sanitize_key', wp_unslash( $_POST['langdesk_allowed_langs'] ) );
		}
		$clean = array_values( array_intersect( $submitted, $valid ) );

		if ( empty( $clean ) ) {
			delete_user_meta( $user_id, self::META_KEY );
		} else {
			update_user_meta( $user_id, self::META_KEY, $clean );
		}
	}
}
