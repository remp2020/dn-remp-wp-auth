<?php


/**
 * Plugin Name: DN REMP WP Auth
 * Plugin URI:  https://dennikn.sk/
 * Description: API to authenticate and retrieve user data from Wordpress
 * Version:     1.0.0
 * Author:      Michal Rusina
 * Author URI:  http://michalrusina.sk/
 * License:     MIT
 */

if ( !defined( 'WPINC' ) ) {
	die;
}


add_action( 'init', 'remp_wp_auth' );
add_action( 'wp_enqueue_scripts', 'remp_login_form_script' );


// Simple login form.
function remp_login_form( $echo = true ) {
	$html = '';

	if ( defined( 'DN_REMP_HOST' ) ) {
		$html = sprintf(
			'
			<form class="remp_login_form" action="%s">
				<input class="remp_login_email" type="email" placeholder="%s">
				<input class="remp_login_password" type="password" placeholder="%s">
				<button class="remp_login_submit" type="submit">%s</button>
			</form>
			',
			DN_REMP_HOST . '/api/v1/users/login/',
			__( 'E-mail', 'dn-remp-wp-auth' ),
			__( 'Password', 'dn-remp-wp-auth' ),
			__( 'Login', 'dn-remp-wp-auth' )
		);
	}
	
	$html = apply_filters( 'remp_login_form_html', $html );

	if ( $echo ) {
		echo $html;
	} else {
		return $html;
	}
}

// Returns user data or false if user is not logged in.
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

	$headers = [
		'Content-Type' => 'application/json',
		'Authorization' => 'Bearer ' . $token
	];

	$response = wp_remote_get( DN_REMP_HOST . $apis[ $data ], [ 'headers' => $headers ] );

	if ( is_wp_error( $response ) ) {
		error_log( 'REMP get_user_subscriptions: ' . $response->get_error_message() );

		return null;
	}

	return $response['body'];
}

// Returns user token.
function remp_get_user_token() {
	if ( isset( $_COOKIE['n_token'] ) ) {
		return $_COOKIE['n_token'];
	} else {
		return false;
	}
}

// Localisation for login form.
function remp_wp_auth() {
	load_plugin_textdomain( 'dn-remp-wp-auth' );
}

// Adds login form handling, if you use your own custom handling feel free to remove_action this.
function remp_login_form_script() {
	wp_register_script( 'dn-remp-wp-auth', plugin_dir_url( __FILE__ ) . 'dn-remp-wp-auth.js', [ 'jquery' ], false, true );
	wp_enqueue_script( 'dn-remp-wp-auth' );	
}

