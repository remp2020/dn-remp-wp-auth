<?php


/**
 * Plugin Name: DN REMP WP Auth
 * Plugin URI:  https://dennikn.sk/
 * Description: Wordpress login, authentification and user data retrieval
 * Version:     1.0.0
 * Author:      Michal Rusina
 * Author URI:  http://michalrusina.sk/
 * License:     MIT
 */

if ( !defined( 'WPINC' ) ) {
	die;
}

function remp_login_form( $echo = true ) {
	$html = '';

	// TODO

	$html = apply_filters( 'remp_login_form_html', $html );

	if ( $echo ) {
		echo $html;
	} else {
		return $html;
	}
}

function remp_get_user( string $data = 'info' ) {
	$apis = [
		'info' => '/api/v1/user/info',
		'subscriptions' => '/api/v1/users/subscriptions'
	];

	if ( !defined( 'DN_REMP_HOST' ) || !in_array( $data, array_keys( $apis ) ) ) {
		return null;
	}

	$token = remp_get_user_token();

	if ( $token === false ) {
		return false;
	}

	$response = wp_remote_get( DN_REMP_HOST . $apis[ $data ], [
		'headers' => [
			'Content-Type:application/json',
			'Authorization: Bearer ' . $token
		]
	] );

	if ( is_wp_error( $response ) ) {
		error_log( 'REMP get_user_subscriptions:' . $response->get_error_message() );

		return null;
	}

	return $response['body'];
}

function remp_get_user_token() {
	if ( isset( $_COOKIE['n_token'] ) ) {
		return $_COOKIE['n_token']
	} else {
		return false;
	}
}