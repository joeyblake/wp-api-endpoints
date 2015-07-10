( function ( window, $ ) {
	'use strict';

	var $load_more_button = $('[data-action="load-more"]'),
		data,
		posts_per_page,
		posts_loaded,
		current_tab;

	//data order is crucial because it becomes part of a url
	data = {
		post_type:  $load_more_button.data( 'post_type' ),
		count:      posts_per_page,
		offset:     posts_loaded[current_tab],
		tab:        current_tab
	};

	//make sure defaults are set
	data = _.defaults( data, {
		post_type:  'all',
		count:      'default',
		offset:     0,
		tab:        0
	});

	data = _.values( data );

	//turn data into a url string
	data = data.join('/');

	//would make http://www.yoursite.com/api/some_endpoint/all/default/0/0
	//For high traffic front end ajax requests pass as a get, no ? so the result can be cached at server by unique url
	wp.ajax.send({
		method: 'GET',
		url: 'http://www.yoursite.com/api/some_endpoint' + data ,
		success: function( response ) {
			//do something
		},
		error: function ( response ) {
			//do something
		}
	} );

	//you aren't limited to GET requests, posts can be done too.
	wp.ajax.send({
		method: 'POST',
		url: 'http://www.yoursite.com/api/another_endpoint/something',
		data: {
			'a_thing': 'value'
		},
		success: function( response ) {
			//do something
		},
		error: function ( response ) {
			//do something
		}
	} );

})( this, jQuery );