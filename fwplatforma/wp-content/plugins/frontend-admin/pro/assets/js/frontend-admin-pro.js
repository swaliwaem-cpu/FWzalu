(function ($, m) {
	var l = acf.Field.extend(
		{
			type: "product_types",

			data: {
				'ftype': 'select'
			},

			select2: false,

			wait: 'load',

			events: {
				'click input[type="radio"]': 'onClickRadio',
				'change select': 'onChooseOption',
			},

			$control: function () {
				return this.$( '.acf-product-types-field' );
			},

			$input: function () {
				return this.getRelatedPrototype().$input.apply( this, arguments );
			},

			$forVariable: function ($el) {
				var $form = $el.parents( 'form' );
				return $form.find( '.acf-field-product-attributes' ).find( 'div[data-name="locations"]' ).find( 'li:last' );
			},

			getRelatedType: function () {

				// vars
				var fieldType = this.get( 'ftype' );

				// normalize
				if (fieldType == 'multi_select' ) {
					fieldType = 'select';
				}

				// return
				return fieldType;

			},

			getRelatedPrototype: function () {
				return acf.getFieldType( this.getRelatedType() ).prototype;
			},

			initialize: function () {
				this.getRelatedPrototype().initialize.apply( this, arguments );
			},

			onClickRadio: function ( e, $el ) {

				// vars
				var $label   = $el.parent( 'label' );
				var selected = $label.hasClass( 'selected' );

				// remove previous selected
				this.$( '.selected' ).removeClass( 'selected' );

				// add active class
				$label.addClass( 'selected' );

				// allow null
				if (this.get( 'allow_null' ) && selected ) {
					$label.removeClass( 'selected' );
					$el.prop( 'checked', false ).trigger( 'change' );
				}
				if (this.$input().val() == 'variable' ) {
					this.$forVariable( $el ).removeClass( 'acf-hidden' );
				} else {
					this.$forVariable( $el ).addClass( 'acf-hidden' );
				}
			},
			onChooseOption: function ( e, $el ) {
				if (this.$input().val() == 'variable' ) {
					this.$forVariable( $el ).removeClass( 'acf-hidden' );
				} else {
					this.$forVariable( $el ).addClass( 'acf-hidden' );
				}

			}
		}
	);
	acf.registerFieldType( l );
	acf.registerConditionForFieldType( 'equalTo', 'product_types' );
	acf.registerConditionForFieldType( 'notEqualTo', 'product_types' );
})( jQuery );

new acf.Model(
	{
		events: {
			'input .pa-custom-name input': "onInputCustomName",
		},
		onInputCustomName:  function (a, b) {
			b.closest( 'div.frontend-block' ).find( '.attr_name' ).text( b.val() );
		},
	}
);

(function ($) {
	var m = acf.Field.extend(
		{
			type: "product_attributes",
			wait: "",
			events: {
				'click [data-name="add-block"]': "onClickAdd",
				'click [data-name="save-changes"]': "onClickSave",
				'click [data-name="duplicate-block"]': "onClickDuplicate",
				'click [data-name="remove-block"]': "onClickRemove",
				'click [data-name="collapse-block"]': "onClickCollapse",
				showField: "onShow",
				unloadField: "onUnload",
				mouseover: "onHover",
			},
			$control: function () {
				return this.$( ".acf-frontend-blocks:first" );
			},
			$blocksWrap: function () {
				return this.$( ".acf-frontend-blocks:first > .values" );
			},
			$blocks: function () {
				return this.$( ".acf-frontend-blocks:first > .values > .frontend-block" );
			},
			$block: function (a) {
				return this.$( ".acf-frontend-blocks:first > .values > .frontend-block:eq(" + a + ")" );
			},
			$clonesWrap: function () {
				return this.$( ".acf-frontend-blocks:first > .clones" );
			},
			$clones: function () {
				return this.$( ".acf-frontend-blocks:first > .clones  > .frontend-block" );
			},
			$clone: function (a) {
				return this.$( '.acf-frontend-blocks:first > .clones  > .frontend-block[data-block="' + a + '"]' );
			},
			$actions: function () {
				return this.$( ".acf-actions:last" );
			},
			$button: function () {
				return this.$( '.acf-actions:last a.add-attrs' );
			},
			$saveButton: function () {
				return this.$( '.acf-actions:last a.save-changes' );
			},
			$forVariations: function () {
				return this.$( 'div[data-name="locations"]' ).find( 'li:last' );
			},
			$productTypeField: function () {
				var $form = this.$el.parents( 'form' );
				return $form.find( '.acf-field-product-types' );
			},
			$productType: function () {
				if (this.$productTypeField().find( 'select' ).val() ) {
					return this.$productTypeField().find( 'select' ).val();
				} else {
					return this.$productTypeField().find( 'input:checked' ).val();
				}
			},
			$popup: function () {
				return this.$( ".tmpl-popup:last" );
			},
			getPopupHTML: function () {
				var a = this.$popup().html();
				a     = $( a );
				var b = this.$blocks(),
				c     = function (d) {
					return b.filter(
						function () {
							return $( this ).data( "block" ) === d;
						}
					).length;
				};
				a.find( "[data-block]" ).each(
					function () {
						var d = $( this ),
						h     = d.data( "min" ) || 0,
						k     = d.data( "max" ) || 0,
						n     = d.data( "block" ) || "",
						f     = c( n );
						if (k && f >= k) {
							d.addClass( "disabled" );
						} else if (h && f < h) {
							k     = h - f;
							f     = acf.__( "{required} {label} {identifier} required (min {min})" );
							var p = acf._n( "block", "blocks", k );
							f     = f.replace( "{required}", k );
							f     = f.replace( "{label}", n );
							f     = f.replace( "{identifier}", p );
							f     = f.replace( "{min}", h );
							d.append( '<span class="badge" title="' + f + '">' + k + "</span>" );
						}
					}
				);
				return (a = a.outerHTML());
			},
			getValue: function () {
				return this.$blocks().length;
			},
			allowRemove: function () {
				var a = parseInt( this.get( "min" ) );
				return ! a || a < this.val();
			},
			allowAdd: function () {
				var a = parseInt( this.get( "max" ) );
				return ! a || a > this.val();
			},
			isFull: function () {
				var a = parseInt( this.get( "max" ) );
				return a && this.val() >= a;
			},
			addSortable: function (a) {
				1 != this.get( "max" ) &&
				this.$blocksWrap().sortable(
					{
						items: "> .frontend-block",
						handle: "> .acf-fc-block-handle",
						forceHelperSize: ! 0,
						forcePlaceholderSize: ! 0,
						scroll: ! 0,
						stop: function (b, c) {
							a.render();
						},
						update: function (b, c) {
							a.$input().trigger( "change" );
						},
					}
				);
			},
			addCollapsed: function () {
				var a = g.load( this.get( "key" ) );
				if ( ! a) {
					return ! 1;
				}
				this.$blocks().each(
					function (b) {
						-1 < a.indexOf( b ) && $( this ).addClass( "-collapsed" );
					}
				);
			},
			addUnscopedEvents: function (a) {
				this.on(
					"invalidField",
					".frontend-block",
					function (b) {
						a.onInvalidField( b, $( this ) );
					}
				);
			},
			initialize: function () {
				this.addUnscopedEvents( this );
				this.addCollapsed();
				acf.disable( this.$clonesWrap(), this.cid );
				this.render();
			},
			render: function () {
				this.$blocks().each(
					function (a) {
						$( this )
						.find( ".acf-frontend-blocks-block-order:first" )
						.html( a + 1 );
					}
				);
				0 == this.val() ? this.$control().addClass( "-empty" ) : this.$control().removeClass( "-empty" );
				this.isFull() ? this.$button().addClass( "disabled" ) : this.$button().removeClass( "disabled" );

				if (this.$productType() != 'variable' ) {
					this.$forVariations().addClass( 'acf-hidden' );
				}
			},
			onShow: function (a, b, c) {
				a = acf.getFields( { is: ":visible", parent: this.$el } );
				acf.doAction( "show_fields", a );
			},
			validateAdd: function () {
				if (this.allowAdd()) {
					return ! 0;
				}
				var a = this.get( "max" ),
				b     = acf.__( "This field has a limit of {max} {label} {identifier}" ),
				c     = acf._n( "block", "blocks", a );
				b     = b.replace( "{max}", a );
				b     = b.replace( "{label}", "" );
				b     = b.replace( "{identifier}", c );
				this.showNotice( { text: b, type: "warning" } );
				return ! 1;
			},
			onClickAdd: function (a, b) {
				if ( ! this.validateAdd()) {
					return ! 1;
				}
				var c = null;
				b.hasClass( "acf-icon" ) && ((c = b.closest( ".frontend-block" )), c.addClass( "-hover" ));
				new l(
					{
						target: b,
						targetConfirm: ! 1,
						text: this.getPopupHTML(),
						context: this,
						confirm: function (d, h) {
							h.hasClass( "disabled" ) || this.add( { block: h.data( "block" ), before: c } );
							c && c.removeClass( "-hover" );
						},
						cancel: function () {
							c && c.removeClass( "-hover" );
						},
					}
				).on( "click", "[data-block]", "onConfirm" );
			},
			add: function (a) {
				a = acf.parseArgs( a, { block: "", before: ! 1 } );
				if ( ! this.allowAdd()) {
					return ! 1;
				}
				var b = acf.duplicate(
					{
						target: this.$clone( a.block ),
						append: this.proxy(
							function (c, d) {
								a.before ? a.before.before( d ) : this.$blocksWrap().append( d );
								acf.enable( d, this.cid );
								this.render();
							}
						),
					}
				);
				this.$input().trigger( "change" );
				return b;
			},
			onClickSave: function ( e, $el ) {
				var self = this;
				self.$saveButton().addClass( "disabled" ).after( '<span class="acf-loading"></span>' );

				var $form         = $el.parents( 'form' );
				var variations    = $form.find( '.acf-field-product-variations' ).first();
				var product_types = $form.find( '.acf-field-product-types' ).first();

				/*  args = {
				form: $form,
				reset: true,
				success: function ($form) { */
					var formData = new FormData( $form[0] );
					formData.append( 'action','frontend_admin/fields/attributes/save_attributes' );
					formData.append( 'attributes',this.$el.data( 'key' ) );
					formData.append( 'variations',variations.data( 'key' ) );
					formData.append( 'product_types',product_types.data( 'key' ) );

					// get HTML
					$.ajax(
						{
							url: acf.get( 'ajaxurl' ),
							data: formData,
							type: 'post',
							cache: false,
							processData: false,
							contentType: false,
							success: function (response) {
								if (response.success ) {
									if (response.data.variations) {
										self.$saveButton().removeClass( "disabled" ).siblings( '.acf-loading' ).remove();
										$form.find( '.acf-field-product-variations' ).replaceWith( response.data.variations );
										acf.doAction( 'append', $form );
										$form.find( 'input[name=_acf_product]' ).val( response.data.product_id );
										$form.find( 'input[name=_acf_form]' ).val( response.data.form_data );
									}
								}
							}
						}
					);
				   /*  }
				}
				acf.validateForm(args); */

			},
			onClickDuplicate: function (a, b) {
				if ( ! this.validateAdd()) {
					return ! 1;
				}
				var c = b.closest( ".frontend-block" );
				this.duplicateBlock( c );
			},
			duplicateBlock: function (a) {
				if ( ! this.allowAdd()) {
					return ! 1;
				}
				var b = this.get( "key" );
				a     = acf.duplicate(
					{
						target: a,
						rename: function (c, d, h, k) {
							return "id" === c ? d.replace( b + "-" + h, b + "-" + k ) : d.replace( b + "][" + h, b + "][" + k );
						},
						before: function (c) {
							acf.doAction( "unmount", c );
						},
						after: function (c, d) {
							acf.doAction( "remount", c );
						},
					}
				);
				this.$input().trigger( "change" );
				this.render();
				acf.focusAttention( a );
				return a;
			},
			validateRemove: function () {
				if (this.allowRemove()) {
					return ! 0;
				}
				var a = this.get( "min" ),
				b     = acf.__( "This field requires at least {min} {label} {identifier}" ),
				c     = acf._n( "block", "blocks", a );
				b     = b.replace( "{min}", a );
				b     = b.replace( "{label}", "" );
				b     = b.replace( "{identifier}", c );
				this.showNotice( { text: b, type: "warning" } );
				return ! 1;
			},
			onClickRemove: function (a, b) {
				var c = b.closest( ".frontend-block" );
				if (a.shiftKey) {
					return this.removeBlock( c );
				}
				c.addClass( "-hover" );
				acf.newTooltip(
					{
						confirmRemove: ! 0,
						target: b,
						context: this,
						confirm: function () {
							this.removeBlock( c );
						},
						cancel: function () {
							c.removeClass( "-hover" );
						},
					}
				);
			},
			removeBlock: function (a) {
				var b = this,
				c     = 1 == this.getValue() ? 60 : 0;
				acf.remove(
					{
						target: a,
						endHeight: c,
						complete: function () {
							b.$input().trigger( "change" );
							b.render();
						},
					}
				);
			},
			onInputCustomName:  function (a, b) {
				b.closest( 'div.frontend-block' ).find( '.attr_name' ).text( b.val() );
			},
			onClickCollapse: function (a, b) {
				var c = b.closest( ".frontend-block" );
				this.isBlockClosed( c ) ? this.openBlock( c ) : this.closeBlock( c );
			},
			isBlockClosed: function (a) {
				return a.hasClass( "-collapsed" );
			},
			openBlock: function (a) {
				a.removeClass( "-collapsed" );
				acf.doAction( "show", a, "collapse" );
			},
			closeBlock: function (a) {
				a.addClass( "-collapsed" );
				acf.doAction( "hide", a, "collapse" );
			},
			onUnload: function () {
				var a = [];
				this.$blocks().each(
					function (b) {
						$( this ).hasClass( "-collapsed" ) && a.push( b );
					}
				);
				a = a.length ? a : null;
				g.save( this.get( "key" ), a );
			},
			onInvalidField: function (a, b) {
				this.isBlockClosed( b ) && this.openBlock( b );
			},
			onHover: function () {
				this.addSortable( this );
				this.off( "mouseover" );
			},
		}
	);
	acf.registerFieldType( m );
	var l = acf.models.TooltipConfirm.extend(
		{
			events: { "click [data-block]": "onConfirm", 'click [data-event="cancel"]': "onCancel" },
			render: function () {
				this.html( this.get( "text" ) );
				this.$el.addClass( "acf-fc-popup" );
			},
		}
	);
	acf.registerConditionForFieldType( "hasValue", "product_attributes" );
	acf.registerConditionForFieldType( "hasNoValue", "product_attributes" );
	acf.registerConditionForFieldType( "lessThan", "product_attributes" );
	acf.registerConditionForFieldType( "greaterThan", "product_attributes" );
	var g = new acf.Model(
		{
			name: "this.collapsedBlocks",
			key: function (a, b) {
				var c = this.get( a + b ) || 0;
				c++;
				this.set( a + b, c, ! 0 );
				1 < c && (a += "-" + c);
				return a;
			},
			load: function (a) {
				a     = this.key( a, "load" );
				var b = acf.getPreference( this.name );
				return b && b[a] ? b[a] : ! 1;
			},
			save: function (a, b) {
				a     = this.key( a, "save" );
				var c = acf.getPreference( this.name ) || {};
				null === b ? delete c[a] : (c[a] = b);
				$.isEmptyObject( c ) && (c = null);
				acf.setPreference( this.name, c );
			},
		}
	);

	var preference = new acf.Model(
		{
			name: 'this.collapsedBlocks',
			key: function (key, context) {
				// vars
				var count = this.get( key + context ) || 0; // update

				count++;
				this.set( key + context, count, true ); // modify fieldKey

				if (count > 1) {
					key += '-' + count;
				} // return

				return key;
			},
			load: function (key) {
				// vars
				var key  = this.key( key, 'load' );
				var data = acf.getPreference( this.name ); // return

				if (data && data[key]) {
					return data[key];
				} else {
					return false;
				}
			},
			save: function (key, value) {
				// vars
				var key  = this.key( key, 'save' );
				var data = acf.getPreference( this.name ) || {}; // delete

				if (value === null) {
					delete data[key]; // append
				} else {
					data[key] = value;
				} // allow null

				if ($.isEmptyObject( data )) {
					data = null;
				} // save

				acf.setPreference( this.name, data );
			}
		}
	);

	var Field = acf.Field.extend(
		{
			type: 'frontend_blocks',
			wait: '',
			events: {
				'click [data-name="add-block"]': 'onClickAdd',
				'click [data-name="duplicate-block"]': 'onClickDuplicate',
				'click [data-name="remove-block"]': 'onClickRemove',
				'click [data-name="collapse-block"]': 'onClickCollapse',
				'showField': 'onShow',
				'unloadField': 'onUnload',
				'mouseover': 'onHover'
			},
			$control: function () {
				return this.$( '.acf-frontend-blocks:first' );
			},
			$blocksWrap: function () {
				return this.$( '.acf-frontend-blocks:first > .values' );
			},
			$blocks: function () {
				return this.$( '.acf-frontend-blocks:first > .values > .frontend-block' );
			},
			$block: function (index) {
				return this.$( '.acf-frontend-blocks:first > .values > .frontend-block:eq(' + index + ')' );
			},
			$clonesWrap: function () {
				return this.$( '.acf-frontend-blocks:first > .clones' );
			},
			$clones: function () {
				return this.$( '.acf-frontend-blocks:first > .clones  > .frontend-block' );
			},
			$clone: function (name) {
				return this.$( '.acf-frontend-blocks:first > .clones  > .frontend-block[data-block="' + name + '"]' );
			},
			$actions: function () {
				return this.$( '.acf-actions:last' );
			},
			$button: function () {
				return this.$( '.acf-actions:last .button' );
			},
			$popup: function () {
				return this.$( '.tmpl-popup:last' );
			},
			getPopupHTML: function () {
				// vars
				var html  = this.$popup().html();
				var $html = $( html ); // count blocks

				var $blocks = this.$blocks();

				var countBlocks = function (name) {
					return $blocks.filter(
						function () {
							return $( this ).data( 'block' ) === name;
						}
					).length;
				}; // modify popup

				$html.find( '[data-block]' ).each(
					function () {
						// vars
						var $a    = $( this );
						var min   = $a.data( 'min' ) || 0;
						var max   = $a.data( 'max' ) || 0;
						var name  = $a.data( 'block' ) || '';
						var count = countBlocks( name ); // max

						if (max && count >= max) {
							$a.addClass( 'disabled' );
							return;
						} // min

						if (min && count < min) {
							// vars
							var required = min - count;

							var title = acf.__( '{required} {label} {identifier} required (min {min})' );

							var identifier = acf._n( 'block', 'blocks', required ); // translate

							title = title.replace( '{required}', required );
							title = title.replace( '{label}', name ); // 5.5.0

							title = title.replace( '{identifier}', identifier );
							title = title.replace( '{min}', min ); // badge

							$a.append( '<span class="badge" title="' + title + '">' + required + '</span>' );
						}
					}
				); // update

				html = $html.outerHTML(); // return

				return html;
			},
			getValue: function () {
				return this.$blocks().length;
			},
			allowRemove: function () {
				var min = parseInt( this.get( 'min' ) );
				return ! min || min < this.val();
			},
			allowAdd: function () {
				var max = parseInt( this.get( 'max' ) );
				return ! max || max > this.val();
			},
			isFull: function () {
				var max = parseInt( this.get( 'max' ) );
				return max && this.val() >= max;
			},
			addSortable: function (self) {
				// bail early if max 1 row
				if (this.get( 'max' ) == 1) {
					return;
				} // add sortable

				this.$blocksWrap().sortable(
					{
						items: '> .frontend-block',
						handle: '> .acf-frontend-blocks-block-handle',
						forceHelperSize: true,
						forcePlaceholderSize: true,
						scroll: true,
						stop: function (event, ui) {
							self.render();
						},
						update: function (event, ui) {
							self.$input().trigger( 'change' );
						}
					}
				);
			},
			addCollapsed: function () {
				// vars
				var indexes = preference.load( this.get( 'key' ) ); // bail early if no collapsed

				if ( ! indexes) {
					return false;
				} // loop

				this.$blocks().each(
					function (i) {
						if (indexes.indexOf( i ) > -1) {
							$( this ).addClass( '-collapsed' );
						}
					}
				);
			},
			addUnscopedEvents: function (self) {
				// invalidField
				this.on(
					'invalidField',
					'.frontend-block',
					function (e) {
						self.onInvalidField( e, $( this ) );
					}
				);
			},
			initialize: function () {
				// add unscoped events
				this.addUnscopedEvents( this ); // add collapsed

				this.addCollapsed(); // disable clone

				acf.disable( this.$clonesWrap(), this.cid ); // render

				this.render();
			},
			render: function () {
				// update order number
				this.$blocks().each(
					function (i) {
						$( this ).find( '.acf-frontend-blocks-block-order:first' ).html( i + 1 );
					}
				); // empty

				if (this.val() == 0) {
					this.$control().addClass( '-empty' );
				} else {
					this.$control().removeClass( '-empty' );
				} // max

				if (this.isFull()) {
					this.$button().addClass( 'disabled' );
				} else {
					this.$button().removeClass( 'disabled' );
				}

			},
			onShow: function (e, $el, context) {
				// get sub fields
				var fields = acf.getFields(
					{
						is: ':visible',
						parent: this.$el
					}
				); // trigger action
				// - ignore context, no need to pass through 'conditional_logic'
				// - this is just for fields like google_map to render itself
				acf.doAction( 'show_fields', fields );
			},
			validateAdd: function () {
				// return true if allowed
				if (this.allowAdd()) {
					return true;
				} // vars

				var max = this.get( 'max' );

				var text = acf.__( 'This field has a limit of {max} {label} {identifier}' );

				var identifier = acf._n( 'block', 'blocks', max ); // replace

				text = text.replace( '{max}', max );
				text = text.replace( '{label}', '' );
				text = text.replace( '{identifier}', identifier ); // add notice

				this.showNotice(
					{
						text: text,
						type: 'warning'
					}
				); // return

				return false;
			},
			onClickAdd: function (e, $el) {
				// validate
				if ( ! this.validateAdd()) {
					return false;
				} // within block

				var $block = null;

				if ($el.hasClass( 'acf-icon' )) {
					$block = $el.closest( '.frontend-block' );
					$block.addClass( '-hover' );
				} // new popup

				var popup = new Popup(
					{
						target: $el,
						targetConfirm: false,
						text: this.getPopupHTML(),
						context: this,
						confirm: function (e, $el) {
							// check disabled
							if ($el.hasClass( 'disabled' )) {
								return;
							} // add

							this.add(
								{
									block: $el.data( 'block' ),
									before: $block
								}
							);
						},
						cancel: function () {
							if ($block) {
								$block.removeClass( '-hover' );
							}
						}
					}
				); // add extra event

				popup.on( 'click', '[data-block]', 'onConfirm' );
			},
			add: function (args) {
				// defaults
				args = acf.parseArgs(
					args,
					{
						block: '',
						before: false
					}
				); // validate

				if ( ! this.allowAdd()) {
					return false;
				} // add row

				var $el = acf.duplicate(
					{
						target: this.$clone( args.block ),
						append: this.proxy(
							function ($el, $el2) {
								// append
								if (args.before) {
									args.before.before( $el2 );
								} else {
									this.$blocksWrap().append( $el2 );
								} // enable

								acf.enable( $el2, this.cid ); // render

								this.render();
							}
						)
					}
				); // trigger change for validation errors

				this.$input().trigger( 'change' ); // return
				$( 'html, body' ).animate(
					{
						scrollTop: $( $el ).closest( '.frontend-block' ).offset().top - 75,
					}
				);
				return $el;
			},
			onClickDuplicate: function (e, $el) {
				// Validate with warning.
				if ( ! this.validateAdd()) {
					return false;
				} // get block and duplicate it.

				var $block = $el.closest( '.frontend-block' );
				this.duplicateBlock( $block );
			},
			duplicateBlock: function ($block) {
				// Validate without warning.
				if ( ! this.allowAdd()) {
					return false;
				} // Vars.

				var fieldKey = this.get( 'key' ); // Duplicate block.

				var $el = acf.duplicate(
					{
						target: $block,
						// Provide a custom renaming callback to avoid renaming parent row attributes.
						rename: function (name, value, search, replace) {
							// Rename id attributes from "field_1-search" to "field_1-replace".
							if (name === 'data-id' || name === 'for') {
								return value.replace( fieldKey + '-' + search, fieldKey + '-' + replace ); // Rename name and for attributes from "[field_1][search]" to "[field_1][replace]".
							} else {
								return value.replace( fieldKey + '][' + search, fieldKey + '][' + replace );
							}
						},
						before: function ($el) {
							acf.doAction( 'unmount', $el );
						},
						after: function ($el, $el2) {
							acf.doAction( 'remount', $el );
						}
					}
				); // trigger change for validation errors

				this.$input().trigger( 'change' ); // Update order numbers.

				this.render(); // Draw focus to block.

				acf.focusAttention( $el ); // Return new block.

				return $el;
			},
			validateRemove: function () {
				// return true if allowed
				if (this.allowRemove()) {
					return true;
				} // vars

				var min = this.get( 'min' );

				var text = acf.__( 'This field requires at least {min} {label} {identifier}' );

				var identifier = acf._n( 'block', 'blocks', min ); // replace

				text = text.replace( '{min}', min );
				text = text.replace( '{label}', '' );
				text = text.replace( '{identifier}', identifier ); // add notice

				this.showNotice(
					{
						text: text,
						type: 'warning'
					}
				); // return

				return false;
			},
			onClickRemove: function (e, $el) {
				var $block = $el.closest( '.frontend-block' ); // Bypass confirmation when holding down "shift" key.

				if (e.shiftKey) {
					return this.removeBlock( $block );
				} // add class

				$block.addClass( '-hover' ); // add tooltip

				var tooltip = acf.newTooltip(
					{
						confirmRemove: true,
						target: $el,
						context: this,
						confirm: function () {
							this.removeBlock( $block );
						},
						cancel: function () {
							$block.removeClass( '-hover' );
						}
					}
				);
			},
			removeBlock: function ($block) {
				// reference
				var self = this; // vars

				var endHeight = this.getValue() == 1 ? 60 : 0; // remove

				acf.remove(
					{
						target: $block,
						endHeight: endHeight,
						complete: function () {
							// trigger change to allow attachment save
							self.$input().trigger( 'change' ); // render

							self.render();
						}
					}
				);
			},
			onClickCollapse: function (e, $el) {
				// vars
				var $block = $el.closest( '.frontend-block' ); // toggle

				if (this.isBlockClosed( $block )) {
					this.openBlock( $block );
				} else {
					this.closeBlock( $block );
				}
			},
			isBlockClosed: function ($block) {
				return $block.hasClass( '-collapsed' );
			},
			openBlock: function ($block) {
				$block.removeClass( '-collapsed' );
				acf.doAction( 'show', $block, 'collapse' );
			},
			closeBlock: function ($block) {
				$block.addClass( '-collapsed' );
				acf.doAction( 'hide', $block, 'collapse' ); // render
				// - no change could happen if block was already closed. Only render when closing

				this.renderBlock( $block );
			},
			renderBlock: function ($block) {
				// vars
				var $input = $block.children( 'input' );
				var prefix = $input.attr( 'name' ).replace( '[fea_block_structure]', '' ); // ajax data

				var ajaxData = {
					action: 'acf/fields/frontend_blocks/block_title',
					field_key: this.get( 'key' ),
					i: $block.index(),
					block: $block.data( 'block' ),
					value: acf.serialize( $block, prefix )
				}; // ajax

				$.ajax(
					{
						url: acf.get( 'ajaxurl' ),
						data: acf.prepareForAjax( ajaxData ),
						dataType: 'html',
						type: 'post',
						success: function (html) {
							if (html) {
								$block.children( '.acf-frontend-blocks-block-handle' ).html( html );
							}
						}
					}
				);
			},
			onUnload: function () {
				// vars
				var indexes = []; // loop

				this.$blocks().each(
					function (i) {
						if ($( this ).hasClass( '-collapsed' )) {
							indexes.push( i );
						}
					}
				); // allow null

				indexes = indexes.length ? indexes : null; // set

				preference.save( this.get( 'key' ), indexes );
			},
			onInvalidField: function (e, $block) {
				// open if is collapsed
				if (this.isBlockClosed( $block )) {
					this.openBlock( $block );
				}
			},
			onHover: function () {
				// add sortable
				this.addSortable( this ); // remove event

				this.off( 'mouseover' );
			}
		}
	);
	acf.registerFieldType( Field );
	/**
	 *  Popup
	 *
	 *  description
	 *
	 * @date  7/4/18
	 * @since 5.6.9
	 *
	 * @param  type $var Description. Default.
	 * @return type Description.
	 */

	var Popup = acf.models.TooltipConfirm.extend(
		{
			events: {
				'click [data-block]': 'onConfirm',
				'click [data-event="cancel"]': 'onCancel'
			},
			render: function () {
				// set HTML
				this.html( this.get( 'text' ) ); // add class

				this.$el.addClass( 'acf-frontend-blocks-popup' );
			}
		}
	);
	/**
	*  conditions
	*
	*  description
	*
	*  @date  9/4/18
	*  @since 5.6.9
	*
	*  @param  type $var Description. Default.
	*  @return type Description.
	*/
	// register existing conditions

	acf.registerConditionForFieldType( 'hasValue', 'frontend_blocks' );
	acf.registerConditionForFieldType( 'hasNoValue', 'frontend_blocks' );
	acf.registerConditionForFieldType( 'lessThan', 'frontend_blocks' );
	acf.registerConditionForFieldType( 'greaterThan', 'frontend_blocks' ); // state

	var Field = acf.Field.extend(
		{

			type: 'product_variations',
			wait: '',

			events: {
				'click a[data-event="add-row"]':         'onClickAdd',
				'click [data-name="save-changes"]':     "onClickSave",
				'click a[data-event="remove-row"]':     'onClickRemove',
				'click .acf-row-handle.order':        'onClickCollapse',
				'showField':                            'onShow',
				'unloadField':                            'onUnload',
				'mouseover':                             'onHover',
			},

			$control: function () {
				return this.$( '.acf-list-item:first' );
			},

			$table: function () {
				return this.$( 'table:first' );
			},

			$tbody: function () {
				return this.$( 'tbody:first' );
			},

			$rows: function () {
				return this.$( 'tbody:first > tr' ).not( '.acf-clone' );
			},

			$row: function ( index ) {
				return this.$( 'tbody:first > tr:eq(' + index + ')' );
			},

			$clone: function () {
				return this.$( 'tbody:first > tr.acf-clone' );
			},

			$actions: function () {
				return this.$( '.acf-actions:last' );
			},

			$button: function () {
				return this.$( '.acf-actions:last .add-variation' );
			},

			$saveButton: function () {
				return this.$( '.acf-actions:last .save-changes' );
			},

			getValue: function () {
				return this.$rows().length;
			},

			allowRemove: function () {
				var min = parseInt( this.get( 'min' ) );
				return ( ! min || min < this.val() );
			},

			allowAdd: function () {
				var max = parseInt( this.get( 'max' ) );
				return ( ! max || max > this.val() );
			},

			addSortable: function ( self ) {

				// bail early if max 1 row
				if (this.get( 'max' ) == 1 ) {
					return;
				}

				// add sortable
				this.$tbody().sortable(
					{
						items: '> tr',
						handle: '> td.order',
						forceHelperSize: true,
						forcePlaceholderSize: true,
						scroll: true,
						stop: function (event, ui) {
							self.render();
						},
						update: function (event, ui) {
							self.$input().trigger( 'change' );
						}
					}
				);
			},

			addCollapsed: function () {

				// vars
				var indexes = preference.load( this.get( 'key' ) );

				// bail early if no collapsed
				if ( ! indexes ) {
					return false;
				}

				// loop
				this.$rows().each(
					function ( i ) {
						if (indexes.indexOf( i ) > -1 ) {
							   $( this ).addClass( '-collapsed' );
						}
					}
				);
			},

			addUnscopedEvents: function ( self ) {

				// invalidField
				this.on(
					'invalidField',
					'.acf-row',
					function (e) {
						var $row = $( this );
						if (self.isCollapsed( $row ) ) {
							   self.expand( $row );
						}
					}
				);
			},

			initialize: function () {

				// add unscoped events
				this.addUnscopedEvents( this );

				// add collapsed
				this.addCollapsed();

				// disable clone
				acf.disable( this.$clone(), this.cid );

				// render
				this.render();
			},

			render: function () {

				// update order number
				/* this.$rows().each(function( i ){
				$(this).find('> .order > span').html( i+1 );
				}); */

				// empty
				if (this.val() == 0 ) {
					this.$control().addClass( '-empty' );
				} else {
					this.$control().removeClass( '-empty' );
				}

				// max
				if (this.allowAdd() ) {
					  this.$button().removeClass( 'disabled' );
				} else {
					  this.$button().addClass( 'disabled' );
				}
			},

			validateAdd: function () {

				// return true if allowed
				if (this.allowAdd() ) {
					return true;
				}

				// vars
				var max  = this.get( 'max' );
				var text = acf.__( 'Maximum rows reached ({max} rows)' );

				// replace
				text = text.replace( '{max}', max );

				// add notice
				this.showNotice(
					{
						text: text,
						type: 'warning'
					}
				);

				// return
				return false;
			},

			onClickAdd: function ( e, $el ) {

				// validate
				if ( ! this.validateAdd() ) {
					return false;
				}

				this.$button().after( '<span class="acf-loading"></span>' );

				var self  = this;
				var $form = $el.parents( 'form' );

				var ajaxData = {
					action:        'frontend_admin/fields/variations/add_variation',
					field_key:    $el.data( 'key' ),
					parent_id:  $form.find( 'input[name=_acf_product]' ).val(),
				};
				   // get HTML
				$.ajax(
					{
						url: acf.get( 'ajaxurl' ),
						data: acf.prepareForAjax( ajaxData ),
						type: 'post',
						dataType: 'json',
						cache: false,
						success: function (response) {
							if (response.data.variation_id) {
								if ($el.hasClass( 'acf-icon' ) ) {
									self.add(
										{
											before: $el.closest( '.acf-row' ),
											variationID: response.data.variation_id
										}
									);
								} else {
									self.add(
										{
											variationID: response.data.variation_id
										}
									);
								}
							}
						}
					  }
				);

			},

			add: function ( args ) {
				// validate
				if ( ! this.allowAdd() ) {
					return false;
				}

				// defaults
				args = acf.parseArgs(
					args,
					{
						before: false
					}
				);

				// add row
				var $el = acf.duplicate(
					{
						target: this.$clone(),
						append: this.proxy(
							function ( $el, $el2 ) {

								 // append
								if (args.before ) {
									args.before.before( $el2 );
								} else {
									$el.before( $el2 );
								}

								// remove clone class
								$el2.removeClass( 'acf-clone -collapsed' );

								$el2.find( '.variation-id' ).html( '#' + args.variationID );
								$el2.find( '.acf-icon.-minus' ).attr( 'data-variation_id',args.variationID );
								$el2.find( '.row-variation-id' ).val( args.variationID );

								// enable
								acf.enable( $el2, this.cid );

								// render
								this.render();

								this.$button().siblings( '.acf-loading' ).remove();

							}
						)
					}
				);

				// trigger change for validation errors
				this.$input().trigger( 'change' );

				// return
				return $el;
			},

			onClickSave: function ( e, $el ) {
				var self = this;

				self.$saveButton().addClass( "disabled" ).after( '<span class="acf-loading"></span>' );

				var $form = $el.parents( 'form' );

				/* args = {
				form: $form,
				reset: true,
				success: function ($form) { */
					var formData = new FormData( $form[0] );
					formData.append( 'action','frontend_admin/fields/variations/save_variations' );
					formData.append( 'field_key',self.get( 'key' ) );

					// get HTML
					$.ajax(
						{
							url: acf.get( 'ajaxurl' ),
							data: formData,
							type: 'post',
							cache: false,
							processData: false,
							contentType: false,
							success: function (response) {
								if (response.data.product_id) {
									self.$saveButton().removeClass( "disabled" ).siblings( '.acf-loading' ).remove();
								} else {
									self.showNotice( { text: response.data, type: "warning" } );
								}
							}
						}
					);
				/* }
				} */

			},
			validateRemove: function () {

				// return true if allowed
				if (this.allowRemove() ) {
					return true;
				}

				// vars
				var min  = this.get( 'min' );
				var text = acf.__( 'Minimum rows reached ({min} rows)' );

				// replace
				text = text.replace( '{min}', min );

				// add notice
				this.showNotice(
					{
						text: text,
						type: 'warning'
					}
				);

				// return
				return false;
			},

			onClickRemove: function ( e, $el ) {

				// vars
				var $row = $el.closest( '.acf-row' );

				// add class
				$row.addClass( '-hover' );

				// add tooltip
				var tooltip = acf.newTooltip(
					{
						confirmRemove: true,
						target: $el,
						context: this,
						confirm: function () {
							 this.remove( $row, $el );
						},
						cancel: function () {
							   $row.removeClass( '-hover' );
						}
					}
				);
			},

			remove: function ( $row, $el ) {

				// reference
				var self = this;

				   var $form = $el.parents( 'form' );

				   var ajaxData = {
						action:        'frontend_admin/fields/variations/remove_variation',
						field_key:    $el.data( 'key' ),
						variation_id:  $el.data( 'variation_id' ),
				};
				   // get HTML
				$.ajax(
					{
						url: acf.get( 'ajaxurl' ),
						data: acf.prepareForAjax( ajaxData ),
						type: 'post',
						dataType: 'json',
						cache: false,
						success: function (response) {
							   // remove row
							acf.remove(
								{
									target: $row,
									endHeight: 0,
									complete: function () {

										// trigger change to allow attachment save
										self.$input().trigger( 'change' );

										// render
										self.render();

										// sync collapsed order
										// self.sync();
									}
								}
							);
						}
					  }
				);
			},

			isCollapsed: function ( $row ) {
				return $row.hasClass( '-collapsed' );
			},

			collapse: function ( $row ) {
				$row.addClass( '-collapsed' );
				acf.doAction( 'hide', $row, 'collapse' );
			},

			expand: function ( $row ) {
				$row.removeClass( '-collapsed' );
				acf.doAction( 'show', $row, 'collapse' );
			},

			onClickCollapse: function ( e, $el ) {
				// vars
				var $row        = $el.closest( '.acf-row' );
				var isCollpased = this.isCollapsed( $row );

				// shift
				if (e.shiftKey ) {
					$row = this.$rows();
				}

				// toggle
				if (isCollpased ) {
					this.expand( $row );
				} else {
					this.collapse( $row );
				}
			},

			onShow: function ( e, $el, context ) {

				// get sub fields
				var fields = acf.getFields(
					{
						is: ':visible',
						parent: this.$el,
					}
				);

				// trigger action
				// - ignore context, no need to pass through 'conditional_logic'
				// - this is just for fields like google_map to render itself
				acf.doAction( 'show_fields', fields );
			},

			onUnload: function () {

				// vars
				var indexes = [];

				// loop
				this.$rows().each(
					function ( i ) {
						if ($( this ).hasClass( '-collapsed' ) ) {
								   indexes.push( i );
						}
					}
				);

				// allow null
				indexes = indexes.length ? indexes : null;

				// set
				preference.save( this.get( 'key' ), indexes );
			},

			onHover: function () {

				// add sortable
				this.addSortable( this );

				// remove event
				this.off( 'mouseover' );
			}
		}
	);

	acf.registerFieldType( Field );

	// register existing conditions
	acf.registerConditionForFieldType( 'hasValue', 'product_variations' );
	acf.registerConditionForFieldType( 'hasNoValue', 'product_variations' );
	acf.registerConditionForFieldType( 'lessThan', 'product_variations' );
	acf.registerConditionForFieldType( 'greaterThan', 'product_variations' );

	var Field = acf.models.ListItemsField.extend(
		{
			type: 'downloadable_files',
		}
	);

	acf.registerFieldType( Field );

	// register existing conditions
	acf.registerConditionForFieldType( 'hasValue', 'downloadable_files' );
	acf.registerConditionForFieldType( 'hasNoValue', 'downloadable_files' );
	acf.registerConditionForFieldType( 'lessThan', 'downloadable_files' );
	acf.registerConditionForFieldType( 'greaterThan', 'downloadable_files' );

	var Field = acf.models.UploadFilesField.extend(
		{
			type: 'product_images',
		}
	);
	acf.registerFieldType( Field );
	// register existing conditions
	acf.registerConditionForFieldType( 'hasValue', 'product_images' );
	acf.registerConditionForFieldType( 'hasNoValue', 'product_images' );
	acf.registerConditionForFieldType( 'selectionLessThan', 'product_images' );
	acf.registerConditionForFieldType( 'selectionGreaterThan', 'product_images' );

		var tfFields = ['manage_stock','sold_individually','is_virtual','is_downloadable', 'product_enable_reviews'];

		$.each(
			tfFields,
			function (index, value) {
				var Field = acf.models.TrueFalseField.extend(
					{
						type: value,
					}
				);
				acf.registerFieldType( Field );
				acf.registerConditionForFieldType( 'equalTo', value );
				acf.registerConditionForFieldType( 'notEqualTo', value );
			}
		);

	var Field = acf.Field.extend(
		{
			type: 'form_step',
			wait: '',
			events: {
				'click .change-step': 'onClickChangeStep',
			},

			$control: function () {
				return this.$( '.frontend-admin-steps' );
			},
			$currentStep: function () {
				var step = this.$control().data( 'current-step' );
				if ( ! step ) {
					step = 1;
				}
				return step;
			},
			$validateSteps: function () {
				return this.$control().data( 'validate-steps' );
			},
			onClickChangeStep: function ( e, $el ) {
				var step        = $el.data( "step" );
				var button      = $el.data( 'button' );
				var currentStep = this.$currentStep();
				if (step == currentStep) {
					return false;
				}

				if (step == 'submit' || ( this.$validateSteps() && button == 'next' ) ) {

					this.$( 'input.step-input' ).val( 1 );
					this.$( '.acf-loading' ).removeClass( 'acf-hidden' );
					this.$( '.button' ).addClass( 'disabled' );
					var field = this;
					var limit = false;
					if (step != 'submit' ) {
						limit = field.$( '.acf-fields[data-step=' + currentStep + ']' );
					}

					args = {
						form: field.$el.parents( 'form' ),
						reset: false,
						limit: limit,
						complete: function ( $form, $validator ) {
							if ($validator.hasErrors() ) {
								var first        = $validator.data.errors[0];
								var subField     = $form.find( 'input[name="' + first.input + '"]' ).closest( '.acf-field' );
								var firstErrorOn = subField.closest( '.acf-fields' ).data( 'step' );
								if (firstErrorOn < currentStep ) {
									field.changeStep( firstErrorOn,currentStep );
								}
								field.$el.find( '.acf-loading' ).addClass( 'acf-hidden' );
								field.$el.find( '.disabled' ).removeClass( 'disabled' );
								$validator.reset();
								return;
							}
							if (step == 'submit' ) {
								acf.submitFrontendForm( $form, false );
							} else {
								field.changeStep( step, currentStep );
								$validator.reset();
							}

						},
					}
					acf.validateFrontendForm( args );
				} else {
					this.changeStep( step, currentStep );
				}
			},

			changeStep: function (step,currentStep) {
				this.$( 'input.step-input' ).val( step );
				this.$( '.form-tab[data-step=' + step + ']' ).addClass( 'active' );
				this.$( '.form-tab[data-step=' + currentStep + ']' ).removeClass( 'active' );
				this.$control().data( 'current-step',step );
				this.$( '.current-step' ).text( step );
				this.$( '.acf-fields[data-step=' + currentStep + ']' ).addClass( 'frontend-admin-hidden' );
				this.$( '.acf-fields[data-step=' + step + ']' ).removeClass( 'frontend-admin-hidden' );
				$( 'body, html' ).animate( {scrollTop:this.$control().offset().top - 100}, 'slow' );

				this.$( '.acf-loading' ).addClass( 'acf-hidden' );
				this.$( '.disabled' ).removeClass( 'disabled' );
			}
		}
	);

	acf.registerFieldType( Field );

})( jQuery );
