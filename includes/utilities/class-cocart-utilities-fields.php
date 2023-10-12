<?php
/**
 * Utilities: Fields class.
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
 * Fields class.
 *
 * @since 4.0.0 Introduced.
 */
class Fields {

	/**
	 * Returns an array of fields based on the configured response requested.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array
	 */
	public static function get_response_from_fields( $request ) {
		$config = ! empty( $request['response'] ) ? trim( $request['response'] ) : '';

		switch ( $config ) {
			case 'mini':
				$fields = array( 'currency', 'items.item_key', 'items.title', 'items.price', 'items.quantity.value', 'items.featured_image', 'totals.subtotal' );
				break;
			case 'digital':
				$fields = array( 'currency', 'customer.billing_address', 'items.item_key', 'items.id', 'items.name', 'items.title', 'items.price', 'items.quantity', 'items.totals', 'items.slug', 'items.meta.product_type', 'items.meta.sku', 'items.meta.variation', 'items.cart_item_data', 'items.featured_image', 'items.extensions', 'coupons', 'needs_payment', 'taxes', 'totals', 'notices' );
				break;
			case 'digital_fees':
				$fields = array( 'currency', 'customer.billing_address', 'items.item_key', 'items.id', 'items.name', 'items.title', 'items.price', 'items.quantity', 'items.totals', 'items.slug', 'items.meta.product_type', 'items.meta.sku', 'items.variation', 'items.cart_item_data', 'items.featured_image', 'items.extensions', 'coupons', 'needs_payment', 'fees', 'taxes', 'totals', 'notices' );
				break;
			case 'shipping':
				$fields = array( 'currency', 'customer', 'items', 'items_weight', 'coupons', 'needs_payment', 'needs_shipping', 'shipping', 'taxes', 'totals', 'notices' );
				break;
			case 'shipping_fees':
				$fields = array( 'currency', 'customer', 'items', 'items_weight', 'coupons', 'needs_payment', 'needs_shipping', 'shipping', 'fees', 'taxes', 'totals', 'notices' );
				break;
			case 'removed_items':
				$fields = array( 'currency', 'removed_items', 'notices' );
				break;
			case 'cross_sells':
				$fields = array( 'currency', 'cross_sells', 'notices' );
				break;
			case 'quick_browse':
				$fields = array( 'id', 'parent_id', 'name', 'type', 'permalink', 'short_description', 'featured', 'prices', 'images', 'external_url', 'add_to_cart' );
				break;
			case 'quick_view':
				$fields = array( 'id', 'parent_id', 'name', 'type', 'permalink', 'short_description', 'featured', 'prices', 'images', 'attributes', 'default_attributes', 'variations', 'stock', 'external_url', 'add_to_cart' );
				break;
			default:
				$fields = array();
				break;
		}

		return $fields;
	} // END get_response_from_fields()

	/**
	 * Return either requested fields or a default set.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param WP_REST_Request $request           The request object.
	 * @param array           $schema            The public item schema data.
	 * @param array           $default_fields    An array of fields preset as the default response.
	 * @param bool            $additional_fields Include registered fields for posts?
	 *
	 * @return array
	 */
	public static function get_fields_for_request( $request, $schema = array(), $default_fields = array(), $additional_fields = false ) {
		/**
		 * Parses additional fields on top of the default fields for the response.
		 *
		 * They may include additional fields added to the cart by
		 * extending the schema from third-party plugins.
		 */
		$args   = self::get_fields_for_response( $request, $schema, $default_fields, $additional_fields );
		$fields = wp_parse_args( $args, $default_fields );

		/**
		 * Filter allows you to set the fields for the request returning.
		 *
		 * @since 4.0.0 Introduced.
		 *
		 * @param array $fields Requested fields, if any.
		 */
		$fields = apply_filters( 'cocart_get_fields_for_request', $fields );

		return $fields;
	} // END get_fields_for_request()

	/**
	 * Gets an array of fields to be included on the response.
	 *
	 * Included fields are based on item schema and `fields=` request argument.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param WP_REST_Request $request           The request object.
	 * @param array           $schema            The public item schema data.
	 * @param array           $default_fields    An array of fields preset as the default response.
	 * @param array           $additional_fields An array of registered fields for posts, if any.
	 *
	 * @return string Fields to be included in the response.
	 */
	public static function get_fields_for_response( $request, $schema = array(), $default_fields = array(), $additional_fields = array() ) {
		$properties = isset( $schema['properties'] ) ? $schema['properties'] : array();
		$properties = empty( $default_fields ) ? $properties : $default_fields;

		// Include registered fields?
		if ( ! empty( $additional_fields ) ) {
			foreach ( $additional_fields as $field_name => $field_options ) {
				/*
				* For back-compat, include any field with an empty schema
				* because it won't be present in $this->get_item_schema().
				*/
				if ( is_null( $field_options['schema'] ) ) {
					$properties[ $field_name ] = $field_options;
				}
			}

			// Exclude fields that specify a different context than the request context.
			$context = $request['context'];
			if ( $context ) {
				foreach ( $properties as $name => $options ) {
					if ( ! empty( $options['context'] ) && ! in_array( $context, $options['context'], true ) ) {
						unset( $properties[ $name ] );
					}
				}
			}
		}

		$fields = array_unique( array_keys( $properties ) );

		if ( ! isset( $request['fields'] ) ) {
			return $fields;
		}

		$requested_fields = wp_parse_list( $request['fields'] );

		// Return all fields if no fields specified.
		if ( 0 === count( $requested_fields ) ) {
			return $fields;
		}

		/*
		 * Requested fields that are not available from the default fields
		 * will be added to the list of fields.
		 */
		if ( ! empty( $default_fields ) ) {
			foreach ( $requested_fields as $field ) {
				if ( ! in_array( $field, $default_fields ) ) {
					if ( cocart_is_field_included( $field, $requested_fields ) ) {
						$fields[] = $field;
					}
				}
			}
		}

		// Trim off outside whitespace from the comma delimited list.
		$requested_fields = array_map( 'trim', $requested_fields );

		if ( $additional_fields ) {
			// Always persist 'id', because it can be needed for add_additional_fields_to_object().
			if ( in_array( 'id', $fields, true ) ) {
				$requested_fields[] = 'id';
			}

			// Always persist 'parent_id' if variations is included without parent product, because it can be needed for add_additional_fields_to_object().
			if ( in_array( 'parent_id', $fields, true ) && $request['include_variations'] ) {
				$requested_fields[] = 'parent_id';
			}
		}

		// Return the list of all requested fields which appear in the schema.
		return array_reduce(
			$requested_fields,
			static function( $response_fields, $field ) use ( $fields ) {
				if ( in_array( $field, $fields, true ) ) {
					$response_fields[] = $field;

					return $response_fields;
				}

				// Check for nested fields if $field is not a direct match.
				$nested_fields = explode( '.', $field );

				// A nested field is included so long as its top-level property
				// is present in the schema.
				if ( in_array( $nested_fields[0], $fields, true ) ) {
					$response_fields[] = $field;
				}

				return $response_fields;
			},
			array()
		);
	} // END get_fields_for_response()

	/**
	 * Gets an array of fields to be excluded on the response.
	 *
	 * Excluded fields are based on item schema and `excluded_fields=` request argument.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @param array           $schema  The public item schema data.
	 *
	 * @return string Fields to be excluded in the response.
	 */
	public static function get_excluded_fields_for_response( $request, $schema = array() ) {
		$properties = isset( $schema['properties'] ) ? $schema['properties'] : array();

		$fields = array_unique( array_keys( $properties ) );

		if ( empty( $request['exclude_fields'] ) ) {
			return array();
		}

		$requested_fields = wp_parse_list( $request['exclude_fields'] );

		// Return all fields if no fields specified.
		if ( 0 === count( $requested_fields ) ) {
			return $fields;
		}

		// Trim off outside whitespace from the comma delimited list.
		$requested_fields = array_map( 'trim', $requested_fields );

		// Return the list of all requested fields which appear in the schema.
		return array_reduce(
			$requested_fields,
			static function( $response_fields, $field ) use ( $fields ) {
				if ( in_array( $field, $fields, true ) ) {
					$response_fields[] = $field;

					return $response_fields;
				}

				// Check for nested fields if $field is not a direct match.
				$nested_fields = explode( '.', $field );

				// A nested field is included so long as its top-level property
				// is present in the schema.
				if ( in_array( $nested_fields[0], $fields, true ) ) {
					$response_fields[] = $field;
				}

				return $response_fields;
			},
			array()
		);
	} // END get_excluded_fields_for_response()

} // END class
