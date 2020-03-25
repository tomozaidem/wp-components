<?php
/**
 * Class adapter to allow use some protected methods from scssc class.
 * Requires scssphp/scss.inc.php package.
 *
 * @author    Tomo Zaidem
 * @package   BrewedTech/WP_Components
 * @version   1.0.0
 */

/**
 * No direct access to this file.
 *
 * @since 1.0.0
 */
defined( 'ABSPATH' ) || die();

/**
 * Class BT_Scssc_Plugin
 */
class BT_Scssc_Plugin extends scssc {

	public function inject( scssc $instance ) {
		$instance->registerFunction(
			'hsvsaturation', array( $this,'function_hsvsaturation' )
		);
	}

	public function convert_color_toHSL($color) {
		return $this->toHSL( $this->coerceColor( $color ) );
	}

	public static function get_instance() {
		static $instance;
		if ( ! $instance ) {
			$instance = new self();
		}
		return $instance;
	}

	public static function function_hsvsaturation($color) {
		$hsv = self::get_instance()->convert_color_toHSL( $color );
		return round( $hsv[2] * 100 ) . '%';
	}

}
