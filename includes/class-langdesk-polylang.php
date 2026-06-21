<?php
defined( 'ABSPATH' ) || exit;

/**
 * The single point of contact with Polylang.
 *
 * INVARIANT: every pll_* call in this plugin goes through here. If Polylang ever
 * changes its public API, this is the only file that needs touching. No other
 * class may call pll_* directly.
 *
 * Each method is defensive (function_exists guard) so a partially-loaded or
 * deactivated Polylang degrades to a safe empty/null result instead of fataling.
 */
final class LangDesk_Polylang {

	/** Is Polylang available and far enough booted to answer language queries? */
	public static function is_active(): bool {
		return function_exists( 'pll_languages_list' ) && function_exists( 'pll_get_post_language' );
	}

	/**
	 * All language slugs configured in Polylang.
	 *
	 * @return string[] e.g. [ 'tr', 'en' ]
	 */
	public static function languages(): array {
		if ( ! function_exists( 'pll_languages_list' ) ) {
			return [];
		}
		$langs = pll_languages_list( [ 'fields' => 'slug' ] );
		return is_array( $langs ) ? array_values( array_map( 'strval', $langs ) ) : [];
	}

	/** Human-readable name for a language slug (for UI labels). */
	public static function language_name( string $slug ): string {
		if ( ! function_exists( 'pll_languages_list' ) ) {
			return $slug;
		}
		$slugs = pll_languages_list( [ 'fields' => 'slug' ] );
		$names = pll_languages_list( [ 'fields' => 'name' ] );
		if ( is_array( $slugs ) && is_array( $names ) ) {
			$idx = array_search( $slug, $slugs, true );
			if ( false !== $idx && isset( $names[ $idx ] ) ) {
				return (string) $names[ $idx ];
			}
		}
		return $slug;
	}

	/** Default (source) language slug. */
	public static function default_language(): string {
		return function_exists( 'pll_default_language' ) ? (string) pll_default_language( 'slug' ) : '';
	}

	/** Currently active language slug in this request. */
	public static function current_language(): string {
		return function_exists( 'pll_current_language' ) ? (string) pll_current_language( 'slug' ) : '';
	}

	/**
	 * Language slug of a post, or '' when it has none / cannot be determined.
	 *
	 * Returning '' is meaningful: the Guard treats "unknown language" as a
	 * fail-closed signal, never as "allowed".
	 */
	public static function post_language( int $post_id ): string {
		if ( ! function_exists( 'pll_get_post_language' ) ) {
			return '';
		}
		$lang = pll_get_post_language( $post_id, 'slug' );
		return is_string( $lang ) ? $lang : '';
	}

	/** Is this post type managed (translatable) by Polylang? */
	public static function is_translated_post_type( string $post_type ): bool {
		if ( function_exists( 'pll_is_translated_post_type' ) ) {
			return (bool) pll_is_translated_post_type( $post_type );
		}
		return false;
	}

	/**
	 * Public post types that Polylang translates.
	 *
	 * @return string[]
	 */
	public static function translated_post_types(): array {
		$types = get_post_types( [ 'public' => true ], 'names' );
		return array_values( array_filter( $types, [ self::class, 'is_translated_post_type' ] ) );
	}

	/**
	 * All post IDs in a given language (any editable status), for the given
	 * translated post types. Used to compute which source posts already have a
	 * translation. Relies on Polylang's `lang` query var.
	 *
	 * @param string[] $types
	 * @return int[]
	 */
	public static function post_ids_in_language( string $lang, array $types ): array {
		if ( empty( $types ) || '' === $lang ) {
			return [];
		}
		$query = new WP_Query(
			[
				'post_type'      => $types,
				'post_status'    => [ 'publish', 'draft', 'pending', 'future', 'private' ],
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'lang'           => $lang,
				'no_found_rows'  => true,
			]
		);
		return array_map( 'intval', $query->posts );
	}

	/**
	 * Map of lang slug => post ID for all translations linked to $post_id
	 * (includes the post itself).
	 *
	 * @return array<string,int>
	 */
	public static function post_translations( int $post_id ): array {
		if ( ! function_exists( 'pll_get_post_translations' ) ) {
			return [];
		}
		$translations = pll_get_post_translations( $post_id );
		return is_array( $translations ) ? array_map( 'intval', $translations ) : [];
	}

	/** Post ID of $post_id's translation in $lang, or 0 if none. */
	public static function translation_in( int $post_id, string $lang ): int {
		if ( ! function_exists( 'pll_get_post' ) ) {
			return 0;
		}
		$translated = pll_get_post( $post_id, $lang );
		return $translated ? (int) $translated : 0;
	}

	/** Assign a language to a post. */
	public static function set_post_language( int $post_id, string $lang ): void {
		if ( function_exists( 'pll_set_post_language' ) ) {
			pll_set_post_language( $post_id, $lang );
		}
	}

	/**
	 * Link a set of posts as translations of each other.
	 *
	 * @param array<string,int> $map lang slug => post ID
	 */
	public static function save_translations( array $map ): void {
		if ( function_exists( 'pll_save_post_translations' ) ) {
			pll_save_post_translations( $map );
		}
	}
}
