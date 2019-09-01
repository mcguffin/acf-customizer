/* global _acf_customizer_location */

/** @namespace wp.customize.acfFieldsetPreview */
console.log("xxxx")
wp.customize.acfFieldsetPreview = wp.customize.AcfFieldsetCustomizerPreview = (function( $, _, wp, api ) {
	var self;

	self = { };

	/**
	 * Init widgets preview.
	 *
	 * @since 0.0.2
	 */
	self.init = function() {
		var self = this;

		self.preview = api.preview;
		// send current location to controls
		api.preview.bind( 'active', function() {

			api.preview.send( 'acf-customize-context', self.current_location );
		} );

		$('[data-acf-customizer]').on('click',function(e){

			var data = $(this).data('acf-customizer');

			api.preview.send( 'acf-focus', data );

		});

	};

	api.bind( 'preview-ready', function() {
		$.extend( self, _acf_customize_context );
		self.init();
	});

	return self;
})( jQuery, _, wp, wp.customize );
