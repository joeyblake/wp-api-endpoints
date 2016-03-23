<?php

require_once '../class-base-api.php';

class MyAjax extends Base_API {
	/**
	 * If you need a different url than "/api" you can set that with this variable
	 * @var string
	 */
	protected static $rewrite_endpoint = 'ajax';

	/**
	 * Possible action endpoints "/ajax/$action" any $action that doesn't exist in the array will return an error
	 * @var array
	 */
	protected static $front_endpoints = array( 'some_endpoint', 'another_endpoint' );

	/**
	 * Possible admin only endpoints, a user without permissions will get an error trying to request this
	 * @var array
	 */
	protected static $admin_endpoints = array( 'an_admin_endpoint' );

	function some_endpoint() {
		//when there are many parameters for a method call, keeping them in order with an array of keys is easier to read
		$keys = array(
			'currentPost',
			'paged',
			'post_type',
			'vertical',
			'posts_per_page',
		);

		//Maps keys to incoming parameters
		if ( $args = array_combine( $keys, func_get_args() ) ) {
			array_walk( $args, array( $this, 'empty_defaults' ) );
			$response = ExampleClass::some_data_method( $args );
			return $response;
		} else {
			return array('Parameters mismatch');
		}
	}

	/**
	 * Arguments can also be used directly in the method
	 * @param $arg1
	 * @param $arg2
	 */
	function another_endpoint( $arg1 = 'default', $arg2 = array() ) {
		if ( $response = another_function_call( $arg1, $arg2 ) ) {
			return $response;
		} else {
			return array('No fake results');
		}
	}

	function an_admin_endpoint( ) {
		$param = sanitize_text_field( $_POST['data'] );
		return SpecialAdmin::doSomething( $param );
	}
}
