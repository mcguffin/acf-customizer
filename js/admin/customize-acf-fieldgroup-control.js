
(function( api, $, options ) {

	api.AcfFieldGroupControl = api.Control.extend({
		initialize: function( id, opt ) {
			var control = this, args;

            args = opt || {};

			api.Control.prototype.initialize.call( control, id, args );

			console.log(id)
		},
		ready: function() {
			var control = this,
				settings = this.setting();

			this.$wrapper = control.container.find('.acf-fields').first();
			acf.do_action( 'append', control.container );

			control.loadForm();

			control.container.on('change','.acf-field', function(e){
				//*
				var $inputs = control.container.find('.acf-field :input');
				control.setting.set( $inputs.serializeJSON() );
				/*/
				console.log('change',control.$inputs.serializeJSON())
				control.setting.set( control.$inputs.serializeJSON() );
				//*/
			});

			api.Control.prototype.ready.apply( control, arguments );
		},
		loadForm: function() {

			var control = this;

			request = wp.ajax.send( 'load_customizer_field_group', {
				data: {
					section_id	: this.id,
					_nonce		: options.load_field_group_nonce,
				}
			} );

			request.done( function(response) {
				control.$wrapper.html( response.html );
				control.$fields = control.container.find('.acf-field');
				control.$inputs = control.container.find('.acf-field :input');
			} );

			request.fail( function( response ) {
			} );

			request.always( function() {
				request = null;
			} );

		}
	});

	//
	//
	// $.each( options.field_group_types, function(i,type){
	 	api.controlConstructor['acf_customizer'] = api.AcfFieldGroupControl;
	// });


})( wp.customize, jQuery, acf_fieldgroup_control );
