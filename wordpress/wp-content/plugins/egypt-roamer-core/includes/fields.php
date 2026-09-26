<?php
/**
 * Field engine: one schema format renders and sanitises post meta boxes,
 * term fields and settings pages, so every admin form behaves the same.
 *
 * Field schema keys:
 *   type     text|textarea|html|url|number|date|select|checkbox|post|posts|image|lines
 *   label    string
 *   help     string (optional)
 *   options  array value => label (select)
 *   post_type string|string[] (post, posts)
 *   default  mixed
 *   step     number step (number)
 */

defined( 'ABSPATH' ) || exit;

/** Allowed inline markup for "html" fields (headlines with <em>, <br>). */
function er_inline_kses(): array {
	return [
		'em'     => [ 'class' => [] ],
		'span'   => [ 'class' => [] ],
		'strong' => [],
		'br'     => [],
		'b'      => [],
		'i'      => [],
		'a'      => [ 'href' => [], 'rel' => [], 'target' => [] ],
	];
}

/** Sanitise one submitted value according to its field schema. */
function er_sanitize_field( array $field, $raw ) {
	switch ( $field['type'] ) {
		case 'textarea':
			return sanitize_textarea_field( (string) $raw );
		case 'html':
			return wp_kses( (string) $raw, er_inline_kses() );
		case 'url':
			$url = esc_url_raw( trim( (string) $raw ), [ 'http', 'https' ] );
			return $url ?: '';
		case 'url_template':
			// Validate with the placeholders swapped for safe values, store with braces intact.
			$raw  = trim( (string) $raw );
			$test = str_replace( [ '{destination}', '{month}', '{adults}' ], [ 'x', '2026-01', '2' ], $raw );
			if ( '' === $raw || preg_match( '/[{}]/', $test ) || ! in_array( wp_parse_url( $test, PHP_URL_SCHEME ), [ 'http', 'https' ], true ) || esc_url_raw( $test, [ 'http', 'https' ] ) !== $test ) {
				return '';
			}
			return $raw;
		case 'number':
			if ( '' === $raw || null === $raw ) {
				return '';
			}
			return is_numeric( $raw ) ? 0 + $raw : '';
		case 'date':
			$raw = trim( (string) $raw );
			return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw ) ? $raw : '';
		case 'select':
			$raw = (string) $raw;
			return array_key_exists( $raw, $field['options'] ?? [] ) ? $raw : (string) ( $field['default'] ?? '' );
		case 'checkbox':
			return $raw ? 1 : 0;
		case 'post':
		case 'image':
			return absint( $raw );
		case 'posts':
			return array_values( array_filter( array_map( 'absint', (array) $raw ) ) );
		case 'lines':
			$lines = preg_split( '/\r\n|\r|\n/', (string) $raw );
			return implode( "\n", array_filter( array_map( 'sanitize_text_field', $lines ), 'strlen' ) );
		case 'text':
		default:
			return sanitize_text_field( (string) $raw );
	}
}

/** Options for relation selects, cached per request. */
function er_relation_options( $post_type ): array {
	static $cache = [];
	$key = implode( ',', (array) $post_type );
	if ( ! isset( $cache[ $key ] ) ) {
		$posts = get_posts( [
			'post_type'        => (array) $post_type,
			'post_status'      => [ 'publish', 'draft', 'pending', 'private', 'future' ],
			'numberposts'      => 500,
			'orderby'          => 'title',
			'order'            => 'ASC',
			'suppress_filters' => false,
			'lang'             => '', // Polylang: all languages
		] );
		$cache[ $key ] = [];
		foreach ( $posts as $p ) {
			$type_obj  = get_post_type_object( $p->post_type );
			$suffix    = 'publish' === $p->post_status ? '' : ' (' . $p->post_status . ')';
			$prefix    = is_array( $post_type ) && count( $post_type ) > 1 && $type_obj ? $type_obj->labels->singular_name . ': ' : '';
			$cache[ $key ][ $p->ID ] = $prefix . ( $p->post_title ?: '#' . $p->ID ) . $suffix;
		}
	}
	return $cache[ $key ];
}

/**
 * Render one field control. $name is the form input name, $value the stored value.
 */
function er_render_field( string $id, string $name, array $field, $value ): void {
	$type = $field['type'];
	echo '<div class="er-field er-field--' . esc_attr( $type ) . '">';
	if ( 'checkbox' !== $type ) {
		printf( '<label class="er-field__label" for="%s">%s</label>', esc_attr( $id ), esc_html( $field['label'] ) );
	}

	switch ( $type ) {
		case 'textarea':
		case 'lines':
			printf(
				'<textarea id="%s" name="%s" rows="%d" class="widefat">%s</textarea>',
				esc_attr( $id ),
				esc_attr( $name ),
				'lines' === $type ? 4 : 3,
				esc_textarea( (string) $value )
			);
			break;
		case 'html':
			printf( '<input type="text" id="%s" name="%s" value="%s" class="widefat" />', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
			break;
		case 'url':
		case 'url_template':
			printf( '<input type="url" id="%s" name="%s" value="%s" class="widefat" placeholder="https://" />', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
			break;
		case 'number':
			printf(
				'<input type="number" id="%s" name="%s" value="%s" step="%s" class="small-text" />',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( (string) $value ),
				esc_attr( (string) ( $field['step'] ?? 'any' ) )
			);
			break;
		case 'date':
			printf( '<input type="date" id="%s" name="%s" value="%s" />', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
			break;
		case 'select':
			printf( '<select id="%s" name="%s">', esc_attr( $id ), esc_attr( $name ) );
			foreach ( $field['options'] as $opt_value => $opt_label ) {
				printf( '<option value="%s"%s>%s</option>', esc_attr( $opt_value ), selected( (string) $value, (string) $opt_value, false ), esc_html( $opt_label ) );
			}
			echo '</select>';
			break;
		case 'checkbox':
			printf(
				'<label for="%s"><input type="checkbox" id="%s" name="%s" value="1"%s /> %s</label>',
				esc_attr( $id ),
				esc_attr( $id ),
				esc_attr( $name ),
				checked( (bool) $value, true, false ),
				esc_html( $field['label'] )
			);
			break;
		case 'post':
			printf( '<select id="%s" name="%s" class="er-select"><option value="0">%s</option>', esc_attr( $id ), esc_attr( $name ), esc_html__( '— None —', 'egypt-roamer-core' ) );
			foreach ( er_relation_options( $field['post_type'] ) as $pid => $title ) {
				printf( '<option value="%d"%s>%s</option>', (int) $pid, selected( (int) $value, (int) $pid, false ), esc_html( $title ) );
			}
			echo '</select>';
			break;
		case 'posts':
			$value = array_map( 'intval', (array) $value );
			// Keep the saved order first so "featured" lists stay ordered, then the rest.
			$options = er_relation_options( $field['post_type'] );
			$ordered = [];
			foreach ( $value as $pid ) {
				if ( isset( $options[ $pid ] ) ) {
					$ordered[ $pid ] = $options[ $pid ];
				}
			}
			$ordered += $options;
			printf( '<select id="%s" name="%s[]" multiple size="6" class="er-select er-select--multi widefat">', esc_attr( $id ), esc_attr( $name ) );
			foreach ( $ordered as $pid => $title ) {
				printf( '<option value="%d"%s>%s</option>', (int) $pid, in_array( (int) $pid, $value, true ) ? ' selected' : '', esc_html( $title ) );
			}
			echo '</select>';
			break;
		case 'image':
			$src = $value ? wp_get_attachment_image_url( (int) $value, 'thumbnail' ) : '';
			printf(
				'<div class="er-image" data-er-image><input type="hidden" id="%s" name="%s" value="%s" /><img src="%s" alt="" %s/> <button type="button" class="button" data-er-image-pick>%s</button> <button type="button" class="button-link" data-er-image-clear>%s</button></div>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( (string) (int) $value ),
				esc_url( (string) $src ),
				$src ? '' : 'hidden ',
				esc_html__( 'Choose image', 'egypt-roamer-core' ),
				esc_html__( 'Remove', 'egypt-roamer-core' )
			);
			break;
		case 'text':
		default:
			printf( '<input type="text" id="%s" name="%s" value="%s" class="widefat" />', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
	}

	if ( ! empty( $field['help'] ) ) {
		echo '<p class="description">' . esc_html( $field['help'] ) . '</p>';
	}
	echo '</div>';
}

/** Admin assets, only on screens that use the field engine. */
add_action( 'admin_enqueue_scripts', static function ( $hook ) {
	$screen = get_current_screen();
	$ours   = $screen && (
		str_starts_with( (string) $screen->post_type, 'er_' )
		|| str_starts_with( (string) $screen->taxonomy, 'er_' )
		|| str_contains( (string) $hook, 'egypt-roamer' )
		|| str_contains( (string) $hook, 'er-' )
	);
	if ( ! $ours ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'er-admin', ER_CORE_URL . 'assets/admin.css', [], ER_CORE_VERSION );
	wp_enqueue_script( 'er-admin', ER_CORE_URL . 'assets/admin.js', [], ER_CORE_VERSION, true );
} );
