<?php
/**
 * Plugin Name:       WPU Orderstatus API
 * Plugin URI:        https://wordpressutveckling.se/egen-rest-api-endpoint-wordpress/
 * Description:       Lägger till REST-endpointen wpu/v1/orderstatus/<id> för WooCommerce: GET läser orderns status, POST sätter den till processing eller completed. Kräver rättigheten manage_woocommerce.
 * Version:           1.0.0
 * Requires at least: 6.7
 * Requires PHP:      8.2
 * Requires Plugins:  woocommerce
 * Author:            Pihlfelt & Vänner
 * Author URI:        https://pihlfelt.se
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

/*
 * GET /wp-json/wpu/v1/orderstatus/<id>
 */

// Routes must be registered on rest_api_init, otherwise WordPress logs a _doing_it_wrong.
add_action( 'rest_api_init', 'wpu_register_order_status_route' );
function wpu_register_order_status_route() {
	register_rest_route(
		'wpu/v1',
		'/orderstatus/(?P<id>\d+)',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'wpu_get_order_status',
			// Order data is not public, so the caller needs the shop manager capability.
			'permission_callback' => function () {
				return current_user_can( 'manage_woocommerce' );
			},
			'args'                => array(
				'id' => array(
					'type'              => 'integer',
					'minimum'           => 1,
					'required'          => true,
					// validate_callback only runs when it is set explicitly.
					'validate_callback' => 'rest_validate_request_arg',
					'sanitize_callback' => 'rest_sanitize_request_arg',
				),
			),
		)
	);
}

function wpu_get_order_status( WP_REST_Request $request ) {
	$order = function_exists( 'wc_get_order' ) ? wc_get_order( $request->get_param( 'id' ) ) : false;
	if ( ! $order ) {
		// The status key decides the HTTP code. Without it WordPress sends 500.
		return new WP_Error( 'wpu_order_not_found', 'Ordern finns inte.', array( 'status' => 404 ) );
	}
	$modified = $order->get_date_modified();
	return rest_ensure_response(
		array(
			'id'       => $order->get_id(),
			'status'   => $order->get_status(),
			'modified' => $modified ? $modified->date( DATE_ATOM ) : null,
		)
	);
}

/*
 * POST /wp-json/wpu/v1/orderstatus/<id> with status=processing|completed
 */

add_action( 'rest_api_init', 'wpu_register_order_status_update_route' );
function wpu_register_order_status_update_route() {
	register_rest_route(
		'wpu/v1',
		'/orderstatus/(?P<id>\d+)',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'wpu_update_order_status',
			'permission_callback' => function () {
				return current_user_can( 'manage_woocommerce' );
			},
			'args'                => array(
				'id'     => array(
					'type'              => 'integer',
					'minimum'           => 1,
					'required'          => true,
					'validate_callback' => 'rest_validate_request_arg',
					'sanitize_callback' => 'rest_sanitize_request_arg',
				),
				'status' => array(
					'type'              => 'string',
					'required'          => true,
					// The warehouse may only move orders between these two states.
					'validate_callback' => function ( $value ) {
						return in_array( $value, array( 'processing', 'completed' ), true );
					},
					'sanitize_callback' => 'sanitize_key',
				),
			),
		)
	);
}

function wpu_update_order_status( WP_REST_Request $request ) {
	$order = function_exists( 'wc_get_order' ) ? wc_get_order( $request->get_param( 'id' ) ) : false;
	if ( ! $order ) {
		return new WP_Error( 'wpu_order_not_found', 'Ordern finns inte.', array( 'status' => 404 ) );
	}
	$order->update_status( $request->get_param( 'status' ), 'Uppdaterad via lagersystemet.' );
	return rest_ensure_response(
		array(
			'id'     => $order->get_id(),
			'status' => $order->get_status(),
		)
	);
}
