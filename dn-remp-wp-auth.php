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

if (!defined('WPINC')) {
	die();
}

class DN_REMP_WP_Auth {
	function __construct() {
		register_activation_hook(__FILE__, [$this, 'register_activation_hook']);
		register_deactivation_hook(__FILE__, 'flush_rewrite_rules');

		add_action('wp_head', [$this, 'wp_head'], 10);
		add_action('wp', [$this, 'wp'], 0);
		add_action('init', [$this, 'init'], 0, 1000);
		add_filter('query_vars', [$this, 'query_vars'], 0);
	}

	function handle_request() {
		$id = false;
		$user = false;

		header('Content-Type: application/json; charset=utf-8');

		if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
			status_header(400);
			echo json_encode([
				'message' => 'Authorization header with Bearer token is not set'
			]);
			exit();
		}
		$parts = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);
		if (count($parts) != 2) {
			status_header(400);
			echo json_encode([
				'message' => 'Authorization header contains invalid structure'
			]);
			exit();
		}
		if (!strtolower($parts[0]) === 'bearer') {
			status_header(400);
			echo json_encode([
				'message' => "Authorization header doesn't contains bearer token"
			]);
			exit();
		}
		if ($parts[1] !== DN_REMP_WP_AUTH_TOKEN) {
			status_header(401);
			echo json_encode([
				'message' => 'Invalid authorization token provided'
			]);
			exit();
		}

		if (isset($_POST['token'])) {
			$id = wp_validate_auth_cookie($_POST['token'], 'logged_in');
		} elseif (isset($_POST['login'])) {
			$user = get_user_by('login', $_POST['login']);
		} elseif (isset($_POST['email'])) {
			$user = get_user_by('email', $_POST['email']);
		}

		if ($user === false && $id === false) {
			status_header(404);
			exit();
		}

		if ($user !== false && wp_check_password($_POST['password'], $user->data->user_pass, $user->ID)) {
			$id = $user->ID;
		}

		if ($id === false) {
			status_header(401);
			echo json_encode([
				'message' => 'Unable to authenticate user'
			]);
			exit();
		}

		$user = get_userdata($id);

		unset($user->data->user_activation_key);
		unset($user->data->user_pass);
		unset($user->caps);
		unset($user->cap_key);
		unset($user->filter);
		unset($user->allcaps);

		$user->data->first_name = get_user_meta($user->ID, 'first_name', true);
		$user->data->last_name = get_user_meta($user->ID, 'last_name', true);

		echo json_encode($user);
		exit();
	}

	function wp_head() {
		$id = get_current_user_id();

		if ($id != 0) {
			printf('<script> var %s = "%s"; </script>', 'DN_REMP_WP_Auth', $_COOKIE[LOGGED_IN_COOKIE]);
		}
	}

	function wp($wp) {
		if (!empty($wp->query_vars['api']) && !empty($wp->query_vars['remp-wp-auth'])) {
			$this->handle_request();
		}
	}

	function init() {
		load_plugin_textdomain('dn-remp-wp-auth');

		add_rewrite_rule('^api/v1/remp/auth?', 'index.php?api=1&remp-wp-auth=1', 'top');

		if (get_option('flush_rewrite_rules')) {
			delete_option('flush_rewrite_rules');
			flush_rewrite_rules();
		}
	}

	function query_vars($vars) {
		return array_unique(array_merge($vars, ['api', 'remp-wp-auth']));
	}

	function register_activation_hook() {
		add_option('flush_rewrite_rules', true);
	}
}

new DN_REMP_WP_Auth();

function dn_remp_wp_auth() {
	$id = get_current_user_id();

	if ($id != 0 && isset($_COOKIE[LOGGED_IN_COOKIE])) {
		return $_COOKIE[LOGGED_IN_COOKIE];
	} else {
		return false;
	}
}
