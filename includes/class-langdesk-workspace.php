<?php
defined( 'ABSPATH' ) || exit;

/**
 * Translator workspace via NATIVE post lists (not custom screens).
 *
 * Polylang's admin language filter is a single, sticky, per-user setting. So:
 *
 *   - "My content": we force a restricted translator's native list to their own
 *     language, so All / Published always show their content (easy to return to).
 *   - "To translate into X": a TRANSIENT view link that shows the source-language
 *     posts for that session only. It must NOT write Polylang's sticky `lang`
 *     filter (that is exactly what got it stuck on the source language before),
 *     so it uses our own query arg and we set the language on the query object
 *     in pre_get_posts (which does not persist).
 *
 * Creation of the missing translation is left to Polylang's native "+" column.
 */
class LangDesk_Workspace {

	/** Our own, non-persisting query arg for the transient "to translate" view. */
	private const VIEW_ARG = 'langdesk_to_translate';

	/** Transient "all in source language" reference view. */
	private const ALL_SOURCE_ARG = 'langdesk_all_source';

	public function register(): void {
		// Late priority so we win over Polylang's own language filtering.
		add_action( 'pre_get_posts', [ $this, 'filter_admin_list' ], 9999 );
		add_action( 'current_screen', [ $this, 'maybe_add_view_links' ] );
	}

	/**
	 * Decide the language of a restricted translator's admin post list.
	 *
	 * Avoids get_current_screen() (not reliable this early); reads the query's own
	 * post_type and the global pagenow instead.
	 */
	public function filter_admin_list( WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( 'edit.php' !== ( $GLOBALS['pagenow'] ?? '' ) ) {
			return;
		}

		$post_type = (string) $query->get( 'post_type' );
		if ( '' === $post_type ) {
			$post_type = 'post';
		}
		if ( ! LangDesk_Polylang::is_translated_post_type( $post_type ) ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( ! LangDesk_Assignment::is_restricted( $user_id ) ) {
			return;
		}

		$langs = LangDesk_Assignment::get_user_languages( $user_id );

		// Transient "to translate" view: show the source language for this request
		// only (set on the query, so Polylang's sticky filter is untouched), and
		// list ONLY the source posts that still lack the target translation.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only list view switch, no state change.
		if ( isset( $_GET[ self::VIEW_ARG ] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only.
			$target = sanitize_key( wp_unslash( $_GET[ self::VIEW_ARG ] ) );
			$source = LangDesk_Polylang::default_language();

			// Valid, distinct target that this user is actually allowed to write.
			if ( '' === $source || $target === $source || ! in_array( $target, $langs, true ) ) {
				return;
			}

			$query->set( 'lang', $source );

			// Exclude source posts that already have a $target translation.
			$excluded = $this->translated_source_ids( $source, $target, [ $post_type ] );
			if ( ! empty( $excluded ) ) {
				$existing = (array) $query->get( 'post__not_in' );
				$query->set( 'post__not_in', array_values( array_unique( array_merge( $existing, $excluded ) ) ) );
			}
			return;
		}

		// Transient "all in source language" reference view: full source list, no
		// missing-only filter. Reading is always allowed, so no target check.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only list view switch, no state change.
		if ( isset( $_GET[ self::ALL_SOURCE_ARG ] ) ) {
			$source = LangDesk_Polylang::default_language();
			if ( '' !== $source ) {
				$query->set( 'lang', $source );
			}
			return;
		}

		// Default "my content" view: pin the list to the translator's own language
		// so their content is always one click away. Single-language only; a
		// multi-language translator keeps seeing all of theirs.
		if ( 1 === count( $langs ) ) {
			$query->set( 'lang', $langs[0] );
		}
	}

	/**
	 * Source-language post IDs that ALREADY have a translation in $target, so the
	 * "to translate" view can exclude them. Derived from the target posts' own
	 * source translations (only translated items are iterated, not all posts).
	 *
	 * @param string[] $types
	 * @return int[]
	 */
	private function translated_source_ids( string $source, string $target, array $types ): array {
		$excluded = [];
		foreach ( LangDesk_Polylang::post_ids_in_language( $target, $types ) as $target_id ) {
			$src = LangDesk_Polylang::translation_in( $target_id, $source );
			if ( $src ) {
				$excluded[] = $src;
			}
		}
		return $excluded;
	}

	/**
	 * Add "To translate into X" links to the views row of a translated post type's
	 * list, for restricted translators. The link uses our own arg, never `lang`,
	 * so it cannot get stuck in Polylang's sticky filter.
	 */
	public function maybe_add_view_links( WP_Screen $screen ): void {
		if ( 'edit' !== $screen->base ) {
			return;
		}
		if ( ! LangDesk_Polylang::is_translated_post_type( (string) $screen->post_type ) ) {
			return;
		}
		if ( ! LangDesk_Assignment::is_restricted( get_current_user_id() ) ) {
			return;
		}
		add_filter( "views_{$screen->id}", [ $this, 'add_view_links' ] );
	}

	/**
	 * @param array<string,string> $views
	 * @return array<string,string>
	 */
	public function add_view_links( array $views ): array {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return $views;
		}

		$source  = LangDesk_Polylang::default_language();
		$targets = LangDesk_Assignment::get_user_languages( get_current_user_id() );

		// "All in {source}" reference view first (full source-language list).
		if ( '' !== $source ) {
			$all_url = add_query_arg(
				[
					'post_type'          => $screen->post_type,
					self::ALL_SOURCE_ARG => 1,
				],
				admin_url( 'edit.php' )
			);
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only active-state check.
			$all_class = isset( $_GET[ self::ALL_SOURCE_ARG ] ) ? ' class="current"' : '';
			$all_label = sprintf(
				/* translators: %s is a language name. */
				esc_html__( 'All in %s', 'langdesk-for-polylang' ),
				esc_html( LangDesk_Polylang::language_name( $source ) )
			);
			$views['langdesk_all_source'] = '<a href="' . esc_url( $all_url ) . '"' . $all_class . '>' . $all_label . '</a>';
		}

		// Then "To translate into X" per target language.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only active-state check.
		$active = isset( $_GET[ self::VIEW_ARG ] );

		foreach ( $targets as $lang ) {
			// A translator of the source language has no separate "to translate"
			// view (their own language is the source).
			if ( $lang === $source || '' === $source ) {
				continue;
			}

			$url = add_query_arg(
				[
					'post_type'    => $screen->post_type,
					self::VIEW_ARG => $lang,
				],
				admin_url( 'edit.php' )
			);

			$label = sprintf(
				/* translators: %s is a language name. */
				esc_html__( 'To translate into %s', 'langdesk-for-polylang' ),
				esc_html( LangDesk_Polylang::language_name( $lang ) )
			);

			$class = $active ? ' class="current"' : '';
			$views[ 'langdesk_' . $lang ] = '<a href="' . esc_url( $url ) . '"' . $class . '>' . $label . '</a>';
		}

		return $views;
	}
}
