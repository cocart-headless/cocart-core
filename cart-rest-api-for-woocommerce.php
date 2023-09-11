<?php
/**
 * Plugin Name: CoCart - Decoupling WooCommerce Made Easy
 * Plugin URI:  https://cocartapi.com
 * Description: CoCart makes it easy to decouple your WooCommerce store via a customizable REST API.
 * Author:      CoCart Headless, LLC
 * Author URI:  https://cocartheadless.com
 * Version:     4.0.0-beta.3
 * Text Domain: cart-rest-api-for-woocommerce
 * Domain Path: /languages/
 * Requires at least: 5.6
 * Requires PHP: 7.4
 *
 * @package CoCart
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'COCART_FILE' ) ) {
	define( 'COCART_FILE', __FILE__ );
}

// Load core packages and the autoloader.
require __DIR__ . '/src/autoloader.php';
require __DIR__ . '/src/packages.php';

if ( ! CoCart\Autoloader::init() ) {
	error_log( 'CoCart Autoloader not found. Please run "npm install" followed by "composer install".' );
	return;
}

CoCart\Packages::init();

// Include the main CoCart class.
if ( ! class_exists( 'CoCart\Core', false ) ) {
	include_once untrailingslashit( plugin_dir_path( COCART_FILE ) ) . '/includes/classes/class-cocart.php';
}

/**
 * Returns the main instance of CoCart and only runs if it does not already exists.
 *
 * @since 2.1.0 Introduced.
 * @since 4.0.0 Updated to Namespaces.
 *
 * @return CoCart
 */
if ( ! function_exists( 'CoCart' ) ) {
	/**
	 * Initialize CoCart.
	 */
	function CoCart() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
		return CoCart\Core::init();
	}

	CoCart();
}
