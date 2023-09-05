<?php
/**
 * Utilities: Monetary Formatting class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Utilities
 * @since   4.0.0 Introduced.
 */

namespace CoCart\Utilities;

use \WP_REST_Request;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Monetary Formatting class.
 *
 * @since 4.0.0 Introduced.
 */
class MonetaryFormatting {

	/**
	 * Returns a monetary value formatted.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param float|string    $value     Money value before formatted.
	 * @param array           $cart_item Cart item data.
	 * @param string          $item_key  Item key of the item in the cart.
	 * @param WP_REST_Request $request   Request used to generate the response.
	 *
	 * @return float|string Money value formatted as a float or string.
	 */
	public static function return_monetary_value( $value, $cart_item, $item_key, $request ) {
		return self::convert_money_response( $value, $request );
	} // END return_monetary_value()

	/**
	 * Formats money values after giving 3rd party plugins
	 * or extensions to manipulate them first.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param float|string    $value   Money value before formatted.
	 * @param WP_REST_Request $request Request used to generate the response.
	 *
	 * @return float|string Money value formatted.
	 */
	public static function convert_money_response( $value, $request ) {
		if ( ! empty( $request['prices'] ) && $request['prices'] === 'formatted' ) {
			return cocart_price_no_html( $value );
		} else {
			return (float) cocart_prepare_money_response( $value );
		}
	} // END convert_money_response()

	/**
	 * Formats cart totals to return as a float or formatted.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param array           $totals  Cart totals.
	 * @param WP_REST_Request $request Request used to generate the response.
	 *
	 * @return array An array of formatted totals.
	 */
	public static function convert_totals_response( $totals, $request ) {
		$totals_converted = array();

		foreach ( $totals as $key => $value ) {
			if ( ! empty( $request['prices'] ) && $request['prices'] === 'formatted' ) {
				$totals_converted[ $key ] = cocart_price_no_html( $value );
			} else {
				$totals_converted[ $key ] = (float) cocart_prepare_money_response( $value );
			}
		}

		$totals = $totals_converted;

		return $totals;
	} // END convert_totals_response()

} // END class