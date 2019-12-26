( function( $ ) {
	'use strict';
	
	$( window ).load( function() {

		// Disable email panel toggle
		$('.rtms_visually_hidden').hide();
		$('#notification_bot_disable_email').change(function () {
			$('#tr_mail_recurrence').slideToggle();
		});

		// initalise the dialog
		$( '.slack_token_help_steps' ).each( function() {
			var current_dialog = '#' + $( this ).attr( 'id' );
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
				},
				create: function() {
					// style fix for WordPress admin
					$( '.ui-dialog-titlebar-close' ).addClass( 'ui-button' );
				},
			} );
		} );

		$( '#toggle-slack-token-help' ).click( function( e ) {
			$( '#slack_token_help_step_1' ).dialog( 'open' );
		} );

		// Previous/next step buttons
		$( '.wpdsa-pagination' ).click( function( e ) {
			var current = '#' + $( this ).closest( '.slack_token_help_steps' ).attr( 'id' );
			var target = $( this ).data( 'target' );
			$( current ).dialog( 'close' );
			$( target ).dialog( 'open' );
		} );

	} );
	
})( jQuery );