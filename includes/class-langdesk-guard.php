<?php
defined( 'ABSPATH' ) || exit;

/**
 * Permission guard. The security core of the plugin.
 *
 * INVARIANT (fail-closed): a restricted user may only write a post whose
 * language is in their allowed set. If the language cannot be determined on a
 * translated post type, access is DENIED, never granted.
 *
 * INVARIANT (map_meta_cap level): enforcement happens on the capability map, not
 * by hiding the edit screen. This makes it cover every write path at once,
 * including the REST API (block editor), quick/bulk edit and page builders such
 * as Elementor, all of which ultimately check `current_user_can('edit_post')`.
 *
 * Reading is never restricted (okuma != yazma): a translator must be able to
 * open the source-language post to translate from it.
 *
 * NEW POSTS: "Add New" creates an empty auto-draft, which Polylang assigns the
 * default language. The guard must not block that (it would show "you attempted
 * to edit an item that doesn't exist"), so auto-drafts are allowed through and
 * their language is pinned to the translator's own language on creation
 * (lock_new_post_language), after which normal enforcement applies.
 */
class LangDesk_Guard {

	/** Meta capabilities that carry a target post ID we must vet. */
	private const GUARDED_CAPS = [ 'edit_post', 'delete_post', 'publish_post' ];

	public function register(): void {
		add_filter( 'map_meta_cap', [ $this, 'map_meta_cap' ], 10, 4 );
		add_action( 'wp_insert_post', [ $this, 'lock_new_post_language' ], 10, 3 );
	}

	/**
	 * @param string[] $caps    Primitive caps WordPress will require.
	 * @param string   $cap     The meta capability being checked.
	 * @param int      $user_id User being checked.
	 * @param array    $args    $args[0] is the post ID for post meta caps.
	 * @return string[]
	 */
	public function map_meta_cap( array $caps, string $cap, int $user_id, array $args ): array {
		if ( ! in_array( $cap, self::GUARDED_CAPS, true ) || empty( $args[0] ) ) {
			return $caps;
		}
		if ( ! LangDesk_Assignment::is_restricted( $user_id ) ) {
			return $caps;
		}

		$post_id   = (int) $args[0];
		$post_type = get_post_type( $post_id );

		// LangDesk only governs post types Polylang actually translates.
		if ( ! $post_type || ! LangDesk_Polylang::is_translated_post_type( $post_type ) ) {
			return $caps;
		}

		// A brand-new empty post: let the editor open. Its language is pinned to
		// the translator's own language in lock_new_post_language(), so normal
		// enforcement kicks in as soon as it becomes a real draft.
		if ( 'auto-draft' === get_post_status( $post_id ) ) {
			return $caps;
		}

		$allowed   = LangDesk_Assignment::get_user_languages( $user_id );
		$post_lang = LangDesk_Polylang::post_language( $post_id );

		// Fail-closed: unknown language, or a language outside the user's set,
		// means no write. '' (no language) on a translated type is a deny.
		if ( '' === $post_lang || ! in_array( $post_lang, $allowed, true ) ) {
			return [ 'do_not_allow' ];
		}

		return $caps;
	}

	/**
	 * Pin a restricted translator's brand-new post to their own language.
	 *
	 * Runs on wp_insert_post (which fires after Polylang's save_post language
	 * assignment, so this wins). Only touches a fresh auto-draft on the new-post
	 * screen whose language is not already one the translator may write.
	 *
	 * @param int     $post_id Inserted post ID.
	 * @param WP_Post $post    Inserted post object.
	 * @param bool    $update  Whether this was an update (vs. a new insert).
	 */
	public function lock_new_post_language( int $post_id, WP_Post $post, bool $update ): void {
		if ( $update || 'auto-draft' !== $post->post_status ) {
			return;
		}
		if ( 'post-new.php' !== ( $GLOBALS['pagenow'] ?? '' ) ) {
			return;
		}
		if ( ! LangDesk_Polylang::is_translated_post_type( (string) $post->post_type ) ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( ! LangDesk_Assignment::is_restricted( $user_id ) ) {
			return;
		}

		$allowed = LangDesk_Assignment::get_user_languages( $user_id );
		if ( empty( $allowed ) ) {
			return;
		}

		// Already an allowed language (e.g. multi-language translator): leave it.
		if ( in_array( LangDesk_Polylang::post_language( $post_id ), $allowed, true ) ) {
			return;
		}

		LangDesk_Polylang::set_post_language( $post_id, $allowed[0] );
	}
}
