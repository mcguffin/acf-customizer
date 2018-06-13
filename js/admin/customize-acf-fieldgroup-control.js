
(function( api, $, options ) {

	// store for acf's static validation callbacks
	var current_control;

	api.AcfFieldGroupControl = api.Control.extend({
		initialize: function( id, opt ) {
			var control = this, args;

            args = opt || {};

			api.Control.prototype.initialize.call( control, id, args );

		},
		ready: function() {
			var control = this,
				settings = this.setting();

			this.$wrapper = control.container.find('.acf-fields').first();
			acf.do_action( 'append', control.container );

			control.load_form();

			control.container.on('change','.acf-field', function(e){
				//*
				// check valid
				var $inputs = control.container.find('.acf-field :input');
				console.log('------change------');
				acf.validation.errors = [];
				$inputs.each(function(){
					this.checkValidity();
				});

				acf.validation.busy = false;
				current_control = control;
				acf.validation.fetch( control.container );
				/*/
				console.log('change',control.$inputs.serializeJSON())
				control.setting.set( control.$inputs.serializeJSON() );
				//*/
			});

			api.Control.prototype.ready.apply( control, arguments );
		},
		load_form: function() {

			var control = this;

			request = wp.ajax.send( 'load_customizer_field_group', {
				data: {
					section_id	: this.id,
					_nonce		: options.load_field_group_nonce,
				}
			} );

			request.done( function(response) {
				control.$wrapper.html( response.html );
				control.$fields = control.container.find('.acf-fields > .acf-field');
				control.init_fields();
				/*
				control.$inputs = control.container.find('.acf-field :input');
				*/
			} );

			request.fail( function( response ) {
			} );

			request.always( function() {
				request = null;
			} );

		},
		init_fields: function() {

			var control = this;

			// will init fields
			acf.do_action('ready', control.$wrapper);
		}
	});

	acf.add_action('validation_success', function(e) {
		var $inputs = current_control.container.find('.acf-field :input');
		current_control.setting.set( $inputs.serializeJSON() );
	});
	acf.add_action('validation_failure',function(e){
		// need to remove acf message because it displays a wrong number of invalid fields
		current_control.container.find('> .acf-error-message').remove();
	});
	acf.add_action('invalid', function($input){
		acf.validation.busy = true;
	});


	//
	//
	// $.each( options.field_group_types, function(i,type){
	 	api.controlConstructor['acf_customizer'] = api.AcfFieldGroupControl;
		api.bind('changeset-error', function(){
			console.log(arguments)
		});
	// });


})( wp.customize, jQuery, acf_fieldgroup_control );
