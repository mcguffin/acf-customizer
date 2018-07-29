
(function( api, $, options ) {

	// store for acf's static validation callbacks
	var current_control,
		acf_customize_context;

	api.AcfFieldGroupControl = api.Control.extend({
		preview_context: {
			type: null,
			id: null,
		},
		initialize: function( id, opt ) {
			var control = this, args;

            args = opt || {};

			api.Control.prototype.initialize.call( control, id, args );

		},
		set_preview_context: function(context) {
			this.preview_context = context;
			return this;
		},
		ready: function() {
			var control = this,
				settings = this.setting();

			if ( 0 <= ['post','term','user'].indexOf( control.params.storage_type ) ) {
				// reload fields if preview url changed
				api.bind( 'acf-customize-context-changed', function( context ) {
					if ( context.type === control.params.storage_type ) {
						control
							.set_preview_context( context )
							.load_form();
					} else {
						control.unload_form();
					}
				} );

			}

			this.$wrapper = control.container.find('.acf-fields').first();
			acf.do_action( 'append', control.container );

			control.load_form();

			control.container.on('change','.acf-field', function(e){
				//*
				// check valid
				var $inputs = control.container.find('.acf-field :input');

				acf.validation.errors = [];
				$inputs.each(function(){
					this.checkValidity();
				});

				acf.validation.busy = false;
				current_control = control;
				acf.validation.fetch( {
					form: control.container,
					success:function($form) {
						// allow for submit
						// acf.validation.ignore = 1;
						// $button.trigger('click');
					},

				} );
				/*/
				console.log('change',control.$inputs.serializeJSON())
				control.setting.set( control.$inputs.serializeJSON() );
				//*/
			});

			api.Control.prototype.ready.apply( control, arguments );
		},
		load_form: function() {

			var control = this;

			request = wp.ajax.send( 'load_customizer_field_groups_'+this.id, {
				data: {
					wp_customize			: 'on',
					section_id				: this.id,
					acf_customize_context	: JSON.stringify( control.preview_context ),
					_nonce					: options.load_field_group_nonce,
				}
			} );

			request.done( function(response) {
				control.$wrapper.html( response.html );
				control.$fields = control.container.find('.acf-fields > .acf-field');
				// control.container.find('.acf-fields [id]').each(function(){
				// 	var id = $(this).attr('id'),
				// 		new_id = control.id + id;
				// 	$(this).attr('id', new_id );
				// 	control.container.find('[for="'+id+'"]').each(function(){
				// 		$(this).attr('for', new_id );
				// 	});
				// });
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
		unload_form: function() {
			var control = this;
			control.$wrapper.html( '' );
		},
		init_fields: function() {

			var control = this;

			// will init fields
			acf.do_action('ready', control.$wrapper);
		},
		updateValues: function() {
			var control = this,
				$inputs = control.container.find('.acf-field :input'),
				value;

			// prefixing numeric keys with a `_`
			// repeater field names have numeric keys, which get casted to int upon serialization.
			// As a result the sorting information is lost ...
			$inputs.each(function(){
				var name = $(this).attr('name');
				if ( ! name ) {
					return;
				}
				$(this).data('prev-name', name );
				$(this).attr('name',name.replace(/\[([0-9]+)\]/g,'\[_$1\]'));
			});

			value = $inputs.serializeJSON({
				useIntKeysAsArrayIndex:false
			});

			// ... restoring field names to previous state
			$inputs.each(function(){
				$(this).attr( 'name', $(this).data('prev-name') );
			});

			// update values.
			control.setting.set( value[this.id] );
		}
	});

	acf.add_action('validation_success', function(e) {
		current_control.updateValues();
	});
	acf.add_action('validation_failure',function(e){
		// need to remove acf message because it displays a wrong number of invalid fields
		current_control.container.find('> .acf-error-message').remove();
	});
	acf.add_action('invalid', function($input){
		acf.validation.busy = true;
	});



	api.bind('ready',function(){
		api.previewer.bind( 'acf-customize-context', function( new_val ) {
			var prev_val;

			prev_val = acf_customize_context
			if ( JSON.stringify(new_val) != JSON.stringify(prev_val) ) {
				api.trigger( 'acf-customize-context-changed', new_val )
			}
			acf_customize_context = new_val;
		});
	});


	api.bind( 'save-request-params', function(query){
		console.log(acf_customize_context)
		_.extend( query, {'acf_customize_context' : JSON.stringify( acf_customize_context )} ) ;
	} );
	//

 	api.controlConstructor['acf_customizer'] = api.AcfFieldGroupControl;

	api.bind('changeset-error', function(){
		//console.log(arguments)
	});


})( wp.customize, jQuery, acf_fieldgroup_control );
