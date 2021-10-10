( function( $ ) {
	'use strict';
	
	$( window ).load( function() {

		$( '.reblex_button_more' ).click( function() {
			var toggle_new = $( this ).attr( 'data-toggle' );
			var toggle_old = $( this ).text();
			$( this ).attr( 'data-toggle', toggle_old );
			$( this ).text( toggle_new );
			$( this ).siblings( '.more_items_class' ).toggleClass( 'opened' );
		} );

		// initalise the dialog
		$( '.reblex_modal' ).each( function() {
			var current_dialog = '#' + $( this ).attr( 'id' );
			var current_iframe = $( current_dialog ).children( 'iframe' );
			var current_iframe_id = $( current_iframe ).attr( 'id' );
			var iframe_content = $( current_iframe).text();
			$( current_iframe).text( '' );
			$( current_dialog ).dialog( {
				title: $( current_dialog ).attr( 'data-title' ),
				dialogClass: 'wp-dialog',
				autoOpen: false,
				draggable: false,
				width: 'auto',
				modal: true,
				resizable: false,
				closeOnEscape: true,
				position: {
					my: 'center',
					at: 'center',
					of: window
				},
				open: function() {
					$('.ui-widget-overlay').bind( 'click', function() {
						$( current_dialog ).dialog( 'close' );
					} )
					var doc = document.getElementById( current_iframe_id ).contentWindow.document;
					doc.open();
					doc.write( iframe_content );
					doc.close();
				},
				create: function() {
					// style fix for WordPress admin
					$( '.ui-dialog-titlebar-close' ).addClass( 'ui-button' );
				},
			} );
		} );

		$( '.reblex_button' ).click( function( e ) {
			var current_modal = $( this ).attr( 'data-target' );
			$( current_modal ).dialog( 'open' );
		} );

	} );
	
})( jQuery );
