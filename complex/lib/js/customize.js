/**
 * Customizer scripts
 *
 * @package     WebMan WordPress Theme Framework
 * @subpackage  Customize
 *
 * @since    1.0
 * @version  1.0
 */





( function( exports, $ ) {





	/**
	 * Custom radio select
	 *
	 * @since    1.0
	 * @version  1.0
	 */

	jQuery( '.custom-radio-container' ).on( 'change', 'input', function() {

		jQuery( this )
			.parent()
				.addClass( 'active' )
				.siblings()
				.removeClass( 'active' );

	} );





} )( wp, jQuery );