<?php
/**
 * Utilities: APIPermission class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Utilities
 * @since   4.0.0 Introduced.
 */

namespace CoCart\Utilities;

use CoCart\DataException;
use WP_REST_Request;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class to handle API permissions.
 *
 * @since 4.0.0 Introduced.
 */
class APIPermission {

	/**
	 * Access token.
	 *
	 * @var string
	 */
	protected static $access_token = '';

	/**
	 * Require Access token.
	 *
	 * @var string
	 */
	protected static $require_access_token = '';

	/**
	 * Requested token.
	 *
	 * @var string
	 */
	protected static $requested_token = '';

	/**
	 * Check whether the access token is required before proceeding
	 * with the request or allow unauthorized access.
	 *
	 * @throws DataException Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has write access, WP_Error object otherwise.
	 */
	public static function has_api_permission( WP_REST_Request $request ) {
		try {
			// Check if we already got the access token from a batch request otherwise get it from settings.
			self::$access_token         = empty( self::$access_token ) ? cocart_get_setting( 'general', 'access_token' ) : self::$access_token;
			self::$require_access_token = empty( self::$require_access_token ) ? cocart_get_setting( 'general', 'require_access_token' ) : self::$require_access_token;

			if ( self::$require_access_token === 'yes' && ! empty( self::$access_token ) ) {
				// Check if we already got the requested token from a batch request otherwise get requested token from header.
				self::$requested_token = empty( self::$requested_token ) ? $request->get_header( 'x-cocart-access-token' ) : self::$requested_token;

				// Validate requested token.
				if ( ! empty( self::$requested_token ) && ! wp_is_uuid( self::$requested_token ) ) {
					throw new DataException( 'cocart_rest_invalid_token', __( 'Invalid token provided.', 'cart-rest-api-for-woocommerce' ), rest_authorization_required_code() );
				}

				// If token matches then proceed.
				if ( self::$access_token == self::$requested_token ) {
					return true;
				} else {
					throw new DataException( 'cocart_rest_permission_denied', __( 'Permission Denied.', 'cart-rest-api-for-woocommerce' ), rest_authorization_required_code() );
				}
			}
		} catch ( DataException $e ) {
			return \CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}

		return true;
	} // END has_api_permission()

} // END class