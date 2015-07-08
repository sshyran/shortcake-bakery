<?php

namespace Shortcake_Bakery\Shortcodes;

class Script extends Shortcode {

	public static function get_shortcode_ui_args() {
		return array(
			'label'          => esc_html__( 'Script', 'shortcake-bakery' ),
			'listItemImage'  => 'dashicons-media-code',
			'attrs'          => array(
				array(
					'label'        => esc_html__( 'URL', 'shortcake-bakery' ),
					'attr'         => 'src',
					'type'         => 'text',
					'description'  => esc_html__( 'Full URL to the script file. Host must be whitelisted.', 'shortcake-bakery' ),
				),
			),
		);
	}

	private static $whitelisted_script_domains = array();

	/**
	 * Get the whitelisted script domains for the plugin
	 */
	public static function get_whitelisted_script_domains() {
		return apply_filters( 'shortcake_bakery_whitelisted_script_domains', static::$whitelisted_script_domains );
	}

	public static function reversal( $content ) {

		$whitelisted_script_domains = static::get_whitelisted_script_domains();
		$shortcode_tag = static::get_shortcode_tag();

		if ( preg_match_all( '!\<script\s[^>]*?src=[\"\']([^\"\']+)[\"\'][^>]*?\>\s{0,}\</script\>!i', $content, $matches ) ) {
			$replacements = array();
			foreach ( $matches[0] as $key => $value ) {
				$url = $matches[1][ $key ];
				$url = ( 0 === strpos( $url, '//' ) ) ? 'http:' .  $url :  $url;
				$host = parse_url( $url, PHP_URL_HOST );
				if ( ! in_array( $host, $whitelisted_script_domains ) ) {
					break;
				}
				$replacements[ $value ] = '[' . $shortcode_tag . ' src="' . esc_url( $url ) . '"][/' . $shortcode_tag . ']';
			}
			$content = str_replace( array_keys( $replacements ), array_values( $replacements ), $content );
		}
		return $content;
	}

	public static function callback( $attrs, $content = '' ) {
		var_dump( $attrs );
		var_dump( $content );

		if ( empty( $attrs['src'] ) ) {
			return '';
		}

		$host = parse_url( $attrs['src'], PHP_URL_HOST );
		var_dump( $host );
		if ( ! in_array( $host, static::get_whitelisted_script_domains() ) ) {
			if ( current_user_can( 'edit_posts' ) ) {
				return '<div class="shortcake-bakery-error"><p>' . sprintf( esc_html__( 'Invalid hostname in URL: %s', 'shortcake-bakery' ), esc_url( $attrs['src'] ) ) . '</p></div>';
			} else {
				return '';
			}
		}

		return '<script src="' . esc_url( $attrs['src'] ) . '"></script>';
	}

}
