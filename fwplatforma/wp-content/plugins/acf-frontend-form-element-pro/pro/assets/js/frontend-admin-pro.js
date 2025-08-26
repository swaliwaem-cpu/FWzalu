(function ($, m) {
    var l = acf.Field.extend({
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
		
		$control: function(){
			return this.$('.acf-product-types-field');
		},
		
		$input: function(){
			return this.getRelatedPrototype().$input.apply(this, arguments);
		},

        $forVariable: function($el){
            var $form = $el.parents('form');
            return $form.find('.acf-field-product-attributes').find('div[data-name="locations"]').find('li:last');
        },

        getRelatedType: function(){
			
			// vars
			var fieldType = this.get('ftype');
			
			// normalize
			if( fieldType == 'multi_select' ) {
				fieldType = 'select';
			}

			// return
			return fieldType;
			
		},
		
		getRelatedPrototype: function(){
			return acf.getFieldType( this.getRelatedType() ).prototype;
		},
		
		initialize: function(){
			this.getRelatedPrototype().initialize.apply(this, arguments);
		},

        onClickRadio: function( e, $el ){
			
			// vars
			var $label = $el.parent('label');
			var selected = $label.hasClass('selected');
			
			// remove previous selected
			this.$('.selected').removeClass('selected');
			
			// add active class
			$label.addClass('selected');
			
			// allow null
			if( this.get('allow_null') && selected ) {
				$label.removeClass('selected');
				$el.prop('checked', false).trigger('change');
			}
            if( this.$input().val() == 'variable' ){
                this.$forVariable($el).removeClass('acf-hidden');
            }else{
                this.$forVariable($el).addClass('acf-hidden');
            }
		},
        onChooseOption: function( e, $el ){
            if( this.$input().val() == 'variable' ){
                this.$forVariable($el).removeClass('acf-hidden');
            }else{
                this.$forVariable($el).addClass('acf-hidden');
            }

        }
    });
    acf.registerFieldType(l);
    acf.registerConditionForFieldType('equalTo', 'product_types');
    acf.registerConditionForFieldType('notEqualTo', 'product_types');
})(jQuery);

(function ($) {
    var m = acf.Field.extend({
        type: "product_attributes",
        wait: "",
        events: {
            'click [data-name="add-layout"]': "onClickAdd",
            'click [data-name="save-changes"]': "onClickSave",
            'click [data-name="duplicate-layout"]': "onClickDuplicate",
            'click [data-name="remove-layout"]': "onClickRemove",
            'click [data-name="collapse-layout"]': "onClickCollapse",
            showField: "onShow",
            unloadField: "onUnload",
            mouseover: "onHover",
        },
        $control: function () {
            return this.$(".acf-frontend-blocks:first");
        },
        $layoutsWrap: function () {
            return this.$(".acf-frontend-blocks:first > .values");
        },
        $layouts: function () {
            return this.$(".acf-frontend-blocks:first > .values > .layout");
        },
        $layout: function (a) {
            return this.$(".acf-frontend-blocks:first > .values > .layout:eq(" + a + ")");
        },
        $clonesWrap: function () {
            return this.$(".acf-frontend-blocks:first > .clones");
        },
        $clones: function () {
            return this.$(".acf-frontend-blocks:first > .clones  > .layout");
        },
        $clone: function (a) {
            return this.$('.acf-frontend-blocks:first > .clones  > .layout[data-layout="' + a + '"]');
        },
        $actions: function () {
            return this.$(".acf-actions:last");
        },
        $button: function () {
            return this.$('.acf-actions:last a.add-attrs');
        },
        $saveButton: function () {
            return this.$('.acf-actions:last a.save-changes');
        },
        $forVariations: function (){
            return this.$('div[data-name="locations"]').find('li:last');
        },
        $productTypeField: function (){
            var $form = this.$el.parents('form');
            return $form.find('.acf-field-product-types');
        },
        $productType: function (){
            if( this.$productTypeField().find('select').val() ){
                return this.$productTypeField().find('select').val();
            }else{
                return this.$productTypeField().find('input:checked').val();
            }
        },
        $popup: function () {
            return this.$(".tmpl-popup:last");
        },
        getPopupHTML: function () {
            var a = this.$popup().html();
            a = $(a);
            var b = this.$layouts(),
                c = function (d) {
                    return b.filter(function () {
                        return $(this).data("layout") === d;
                    }).length;
                };
            a.find("[data-layout]").each(function () {
                var d = $(this),
                    h = d.data("min") || 0,
                    k = d.data("max") || 0,
                    n = d.data("layout") || "",
                    f = c(n);
                if (k && f >= k) d.addClass("disabled");
                else if (h && f < h) {
                    k = h - f;
                    f = acf.__("{required} {label} {identifier} required (min {min})");
                    var p = acf._n("layout", "layouts", k);
                    f = f.replace("{required}", k);
                    f = f.replace("{label}", n);
                    f = f.replace("{identifier}", p);
                    f = f.replace("{min}", h);
                    d.append('<span class="badge" title="' + f + '">' + k + "</span>");
                }
            });
            return (a = a.outerHTML());
        },
        getValue: function () {
            return this.$layouts().length;
        },
        allowRemove: function () {
            var a = parseInt(this.get("min"));
            return !a || a < this.val();
        },
        allowAdd: function () {
            var a = parseInt(this.get("max"));
            return !a || a > this.val();
        },
        isFull: function () {
            var a = parseInt(this.get("max"));
            return a && this.val() >= a;
        },
        addSortable: function (a) {
            1 != this.get("max") &&
                this.$layoutsWrap().sortable({
                    items: "> .layout",
                    handle: "> .acf-fc-layout-handle",
                    forceHelperSize: !0,
                    forcePlaceholderSize: !0,
                    scroll: !0,
                    stop: function (b, c) {
                        a.render();
                    },
                    update: function (b, c) {
                        a.$input().trigger("change");
                    },
                });
        },
        addCollapsed: function () {
            var a = g.load(this.get("key"));
            if (!a) return !1;
            this.$layouts().each(function (b) {
                -1 < a.indexOf(b) && $(this).addClass("-collapsed");
            });
        },
        addUnscopedEvents: function (a) {
            this.on("invalidField", ".layout", function (b) {
                a.onInvalidField(b, $(this));
            });
        },
        initialize: function () {
            this.addUnscopedEvents(this);
            this.addCollapsed();
            acf.disable(this.$clonesWrap(), this.cid);
            this.render();
        },
        render: function () {
            this.$layouts().each(function (a) {
                $(this)
                    .find(".acf-frontend-blocks-layout-order:first")
                    .html(a + 1);
            });
            0 == this.val() ? this.$control().addClass("-empty") : this.$control().removeClass("-empty");
            this.isFull() ? this.$button().addClass("disabled") : this.$button().removeClass("disabled");

            if( this.$productType() != 'variable' ){
                this.$forVariations().addClass('acf-hidden');
            }
        },
        onShow: function (a, b, c) {
            a = acf.getFields({ is: ":visible", parent: this.$el });
            acf.doAction("show_fields", a);
        },
        validateAdd: function () {
            if (this.allowAdd()) return !0;
            var a = this.get("max"),
                b = acf.__("This field has a limit of {max} {label} {identifier}"),
                c = acf._n("layout", "layouts", a);
            b = b.replace("{max}", a);
            b = b.replace("{label}", "");
            b = b.replace("{identifier}", c);
            this.showNotice({ text: b, type: "warning" });
            return !1;
        },
        onClickAdd: function (a, b) {
            if (!this.validateAdd()) return !1;
            var c = null;
            b.hasClass("acf-icon") && ((c = b.closest(".layout")), c.addClass("-hover"));
            new l({
                target: b,
                targetConfirm: !1,
                text: this.getPopupHTML(),
                context: this,
                confirm: function (d, h) {
                    h.hasClass("disabled") || this.add({ layout: h.data("layout"), before: c });
                    c && c.removeClass("-hover");
                },
                cancel: function () {
                    c && c.removeClass("-hover");
                },
            }).on("click", "[data-layout]", "onConfirm");
        },
        add: function (a) {
            a = acf.parseArgs(a, { layout: "", before: !1 });
            if (!this.allowAdd()) return !1;
            var b = acf.duplicate({
                target: this.$clone(a.layout),
                append: this.proxy(function (c, d) {
                    a.before ? a.before.before(d) : this.$layoutsWrap().append(d);
                    acf.enable(d, this.cid);
                    this.render();
                }),
            });
            this.$input().trigger("change");
            return b;
        },
        onClickSave: function( e, $el ){
            var self = this;
            self.$saveButton().addClass("disabled").after('<span class="acf-loading"></span>');

            var $form = $el.parents('form');
            var variations = $form.find('.acf-field-product-variations').first();
            var product_types = $form.find('.acf-field-product-types').first();

           /*  args = {
                form: $form,
                reset: true,
                success: function ($form) { */
                    var formData = new FormData($form[0]);          
                    formData.append('action','frontend_admin/fields/attributes/save_attributes');
                    formData.append('attributes',this.$el.data('key'));
                    formData.append('variations',variations.data('key'));
                    formData.append('product_types',product_types.data('key'));

                    // get HTML
                    $.ajax({
                        url: acf.get('ajaxurl'),
                        data: formData,
                        type: 'post',
                        cache: false,
                        processData: false,
                        contentType: false,
                        success: function(response){
                            if( ! response.success ){
                                console.log(response.data);
                            }else{
                                if(response.data.variations){
                                    console.log(response);
                                    self.$saveButton().removeClass("disabled").siblings('.acf-loading').remove();
                                    $form.find('.acf-field-product-variations').replaceWith(response.data.variations);
                                    acf.doAction('append', $form);
                                    $form.find('input[name=_acf_product]').val(response.data.product_id);
                                    $form.find('input[name=_acf_form]').val(response.data.form_data);
                                }
                            }
                        }
                    });
               /*  }
            }
            acf.validateForm(args); */

        },
        onClickDuplicate: function (a, b) {
            if (!this.validateAdd()) return !1;
            var c = b.closest(".layout");
            this.duplicateLayout(c);
        },
        duplicateLayout: function (a) {
            if (!this.allowAdd()) return !1;
            var b = this.get("key");
            a = acf.duplicate({
                target: a,
                rename: function (c, d, h, k) {
                    return "id" === c ? d.replace(b + "-" + h, b + "-" + k) : d.replace(b + "][" + h, b + "][" + k);
                },
                before: function (c) {
                    acf.doAction("unmount", c);
                },
                after: function (c, d) {
                    acf.doAction("remount", c);
                },
            });
            this.$input().trigger("change");
            this.render();
            acf.focusAttention(a);
            return a;
        },
        validateRemove: function () {
            if (this.allowRemove()) return !0;
            var a = this.get("min"),
                b = acf.__("This field requires at least {min} {label} {identifier}"),
                c = acf._n("layout", "layouts", a);
            b = b.replace("{min}", a);
            b = b.replace("{label}", "");
            b = b.replace("{identifier}", c);
            this.showNotice({ text: b, type: "warning" });
            return !1;
        },
        onClickRemove: function (a, b) {
            var c = b.closest(".layout");
            if (a.shiftKey) return this.removeLayout(c);
            c.addClass("-hover");
            acf.newTooltip({
                confirmRemove: !0,
                target: b,
                context: this,
                confirm: function () {
                    this.removeLayout(c);
                },
                cancel: function () {
                    c.removeClass("-hover");
                },
            });
        },
        removeLayout: function (a) {
            var b = this,
                c = 1 == this.getValue() ? 60 : 0;
            acf.remove({
                target: a,
                endHeight: c,
                complete: function () {
                    b.$input().trigger("change");
                    b.render();
                },
            });
        },
        onClickCollapse: function (a, b) {
            var c = b.closest(".layout");
            this.isLayoutClosed(c) ? this.openLayout(c) : this.closeLayout(c);
        },
        isLayoutClosed: function (a) {
            return a.hasClass("-collapsed");
        },
        openLayout: function (a) {
            a.removeClass("-collapsed");
            acf.doAction("show", a, "collapse");
        },
        closeLayout: function (a) {
            a.addClass("-collapsed");
            acf.doAction("hide", a, "collapse");
        },
        onUnload: function () {
            var a = [];
            this.$layouts().each(function (b) {
                $(this).hasClass("-collapsed") && a.push(b);
            });
            a = a.length ? a : null;
            g.save(this.get("key"), a);
        },
        onInvalidField: function (a, b) {
            this.isLayoutClosed(b) && this.openLayout(b);
        },
        onHover: function () {
            this.addSortable(this);
            this.off("mouseover");
        },
    });
    acf.registerFieldType(m);
    var l = acf.models.TooltipConfirm.extend({
        events: { "click [data-layout]": "onConfirm", 'click [data-event="cancel"]': "onCancel" },
        render: function () {
            this.html(this.get("text"));
            this.$el.addClass("acf-fc-popup");
        },
    });
    acf.registerConditionForFieldType("hasValue", "product_attributes");
    acf.registerConditionForFieldType("hasNoValue", "product_attributes");
    acf.registerConditionForFieldType("lessThan", "product_attributes");
    acf.registerConditionForFieldType("greaterThan", "product_attributes");
    var g = new acf.Model({
        name: "this.collapsedLayouts",
        key: function (a, b) {
            var c = this.get(a + b) || 0;
            c++;
            this.set(a + b, c, !0);
            1 < c && (a += "-" + c);
            return a;
        },
        load: function (a) {
            a = this.key(a, "load");
            var b = acf.getPreference(this.name);
            return b && b[a] ? b[a] : !1;
        },
        save: function (a, b) {
            a = this.key(a, "save");
            var c = acf.getPreference(this.name) || {};
            null === b ? delete c[a] : (c[a] = b);
            $.isEmptyObject(c) && (c = null);
            acf.setPreference(this.name, c);
        },
    });

    
})(jQuery);


(function($){
	
	var Field = acf.Field.extend({
		
		type: 'product_variations',
		wait: '',
		
		events: {
			'click a[data-event="add-row"]': 		'onClickAdd',
            'click [data-name="save-changes"]':     "onClickSave",
			'click a[data-event="remove-row"]': 	'onClickRemove',
			'click .acf-row-handle.order':        'onClickCollapse',
			'showField':							'onShow',
			'unloadField':							'onUnload',
			'mouseover': 							'onHover',
		},
		
		$control: function(){
			return this.$('.acf-list-item:first');
		},
		
		$table: function(){
			return this.$('table:first');
		},
		
		$tbody: function(){
			return this.$('tbody:first');
		},
		
		$rows: function(){
			return this.$('tbody:first > tr').not('.acf-clone');
		},
		
		$row: function( index ){
			return this.$('tbody:first > tr:eq(' + index + ')');
		},
		
		$clone: function(){
			return this.$('tbody:first > tr.acf-clone');
		},
		
		$actions: function(){
			return this.$('.acf-actions:last');
		},
		
		$button: function(){
			return this.$('.acf-actions:last .add-variation');
		},

        $saveButton: function(){
			return this.$('.acf-actions:last .save-changes');
		},
		
		getValue: function(){
			return this.$rows().length;
		},
		
		allowRemove: function(){
			var min = parseInt( this.get('min') );
			return ( !min || min < this.val() );
		},
		
		allowAdd: function(){
			var max = parseInt( this.get('max') );
			return ( !max || max > this.val() );
		},
		
		addSortable: function( self ){
			
			// bail early if max 1 row
			if( this.get('max') == 1 ) {
				return;
			}
			
			// add sortable
			this.$tbody().sortable({
				items: '> tr',
				handle: '> td.order',
				forceHelperSize: true,
				forcePlaceholderSize: true,
				scroll: true,
	   			stop: function(event, ui) {
					self.render();
	   			},
	   			update: function(event, ui) {
					self.$input().trigger('change');
		   		}
			});
		},
		
		addCollapsed: function(){
			
			// vars
			var indexes = preference.load( this.get('key') );
			
			// bail early if no collapsed
			if( !indexes ) {
				return false;
			}
			
			// loop
			this.$rows().each(function( i ){
				if( indexes.indexOf(i) > -1 ) {
					$(this).addClass('-collapsed');
				}
			});
		},
		
		addUnscopedEvents: function( self ){
			
			// invalidField
			this.on('invalidField', '.acf-row', function(e){
				var $row = $(this);
				if( self.isCollapsed($row) ) {
					self.expand( $row );
				}
			});
		},
				
		initialize: function(){
			
			// add unscoped events
			this.addUnscopedEvents( this );
			
			// add collapsed
			this.addCollapsed();
			
			// disable clone
			acf.disable( this.$clone(), this.cid );
			
			// render
			this.render();
		},
		
		render: function(){
			
			// update order number
			/* this.$rows().each(function( i ){
				$(this).find('> .order > span').html( i+1 );
			}); */
			
			// empty
			if( this.val() == 0 ) {
				this.$control().addClass('-empty');
			} else {
				this.$control().removeClass('-empty');
			}
			
			// max
			if( this.allowAdd() ) {
				this.$button().removeClass('disabled');
			} else {
				this.$button().addClass('disabled');
			}	
		},
		
		validateAdd: function(){
			
			// return true if allowed
			if( this.allowAdd() ) {
				return true;
			}
			
			// vars
			var max = this.get('max');
			var text = acf.__('Maximum rows reached ({max} rows)');
			
			// replace
			text = text.replace('{max}', max);
			
			// add notice
			this.showNotice({
				text: text,
				type: 'warning'
			});
			
			// return
			return false;
		},
		
		onClickAdd: function( e, $el ){
			
			// validate
			if( !this.validateAdd() ) {
				return false;
			}

            this.$button().after('<span class="acf-loading"></span>');

			var self = this;
            var $form = $el.parents('form');

            var ajaxData = {
                action:		'frontend_admin/fields/variations/add_variation',
                field_key:	$el.data('key'),
                nonce:      $form.find('input[name=_acf_nonce]').val(),
                parent_id:  $form.find('input[name=_acf_product]').val(),
            };
            // get HTML
            $.ajax({
                url: acf.get('ajaxurl'),
                data: acf.prepareForAjax(ajaxData),
                type: 'post',
                dataType: 'json',
                cache: false,
                success: function(response){
                    if(response.data.variation_id){
                        if( $el.hasClass('acf-icon') ) {
                            self.add({
                                before: $el.closest('.acf-row'),
                                variationID: response.data.variation_id
                            });
                        } else {
                            self.add({
                                variationID: response.data.variation_id
                            });
                        }
                    }
                }
            });

		},
		
		add: function( args ){
			// validate
			if( !this.allowAdd() ) {
				return false;
			}
			
			// defaults
			args = acf.parseArgs(args, {
				before: false
			});
			
			// add row
			var $el = acf.duplicate({
				target: this.$clone(),
				append: this.proxy(function( $el, $el2 ){
					
					// append
					if( args.before ) {
						args.before.before( $el2 );
					} else {
						$el.before( $el2 );
					}
					
					// remove clone class
					$el2.removeClass('acf-clone -collapsed');

                    $el2.find('.variation-id').html('#'+args.variationID);
                    $el2.find('.acf-icon.-minus').attr('data-variation_id',args.variationID);
                    $el2.find('.row-variation-id').val(args.variationID);

					// enable
					acf.enable( $el2, this.cid );
					
					// render
					this.render();

                    this.$button().siblings('.acf-loading').remove();

				})
			});
			
			// trigger change for validation errors
			this.$input().trigger('change');
			
			// return
			return $el;
		},

        onClickSave: function( e, $el ){
            var self = this;

            self.$saveButton().addClass("disabled").after('<span class="acf-loading"></span>');

            var $form = $el.parents('form');

            /* args = {
                form: $form,
                reset: true,
                success: function ($form) { */
                    var formData = new FormData($form[0]);          
                    formData.append('action','frontend_admin/fields/variations/save_variations');
                    formData.append('field_key',$el.data('key'));

                    // get HTML
                    $.ajax({
                        url: acf.get('ajaxurl'),
                        data: formData,
                        type: 'post',
                        cache: false,
                        processData: false,
                        contentType: false,
                        success: function(response){
                            if(response.data.product_id){
                                self.$saveButton().removeClass("disabled").siblings('.acf-loading').remove();
                            }
                        }
                    });
                /* }
            } */
            
        },
		validateRemove: function(){
			
			// return true if allowed
			if( this.allowRemove() ) {
				return true;
			}
			
			// vars
			var min = this.get('min');
			var text = acf.__('Minimum rows reached ({min} rows)');
			
			// replace
			text = text.replace('{min}', min);
			
			// add notice
			this.showNotice({
				text: text,
				type: 'warning'
			});
			
			// return
			return false;
		},
		
		onClickRemove: function( e, $el ){
			
			// vars
			var $row = $el.closest('.acf-row');
			
			// add class
			$row.addClass('-hover');
			
			// add tooltip
			var tooltip = acf.newTooltip({
				confirmRemove: true,
				target: $el,
				context: this,
				confirm: function(){
					this.remove( $row, $el );
				},
				cancel: function(){
					$row.removeClass('-hover');
				}
			});
		},

		remove: function( $row, $el ){
			
			// reference
			var self = this;
			
            var $form = $el.parents('form');

            var ajaxData = {
                action:		'frontend_admin/fields/variations/remove_variation',
                field_key:	$el.data('key'),
                nonce:      $form.find('input[name=_acf_nonce]').val(),
                variation_id:  $el.data('variation_id'),
            };
            // get HTML
            $.ajax({
                url: acf.get('ajaxurl'),
                data: acf.prepareForAjax(ajaxData),
                type: 'post',
                dataType: 'json',
                cache: false,
                success: function(response){
                   	// remove row
                    acf.remove({
                        target: $row,
                        endHeight: 0,
                        complete: function(){
                            
                            // trigger change to allow attachment save
                            self.$input().trigger('change');
                        
                            // render
                            self.render();
                            
                            // sync collapsed order
                            //self.sync();
                        }
                    });
                }
            });
		},
		
		isCollapsed: function( $row ){
			return $row.hasClass('-collapsed');
		},
		
		collapse: function( $row ){
			$row.addClass('-collapsed');
			acf.doAction('hide', $row, 'collapse');
		},
		
		expand: function( $row ){
			$row.removeClass('-collapsed');
			acf.doAction('show', $row, 'collapse');
		},
		
		onClickCollapse: function( e, $el ){
			// vars
			var $row = $el.closest('.acf-row');
			var isCollpased = this.isCollapsed( $row );
			
			// shift
			if( e.shiftKey ) {
				$row = this.$rows();
			}
			
			// toggle
			if( isCollpased ) {
				this.expand( $row );
			} else {
				this.collapse( $row );
			}	
		},
		
		onShow: function( e, $el, context ){
			
			// get sub fields
			var fields = acf.getFields({
				is: ':visible',
				parent: this.$el,
			});
			
			// trigger action
			// - ignore context, no need to pass through 'conditional_logic'
			// - this is just for fields like google_map to render itself
			acf.doAction('show_fields', fields);
		},
		
		onUnload: function(){
			
			// vars
			var indexes = [];
			
			// loop
			this.$rows().each(function( i ){
				if( $(this).hasClass('-collapsed') ) {
					indexes.push( i );
				}
			});
			
			// allow null
			indexes = indexes.length ? indexes : null;
			
			// set
			preference.save( this.get('key'), indexes );
		},
		
		onHover: function(){
			
			// add sortable
			this.addSortable( this );
			
			// remove event
			this.off('mouseover');
		}
	});
	
	acf.registerFieldType( Field );
	
	// register existing conditions
	acf.registerConditionForFieldType('hasValue', 'product_variations');
	acf.registerConditionForFieldType('hasNoValue', 'product_variations');
	acf.registerConditionForFieldType('lessThan', 'product_variations');
	acf.registerConditionForFieldType('greaterThan', 'product_variations');

	
    var Field = acf.models.ListItemsField.extend({        
        type: 'downloadable_files',
    });
    
    acf.registerFieldType( Field );
    
    
    // register existing conditions
    acf.registerConditionForFieldType('hasValue', 'downloadable_files');
    acf.registerConditionForFieldType('hasNoValue', 'downloadable_files');
    acf.registerConditionForFieldType('lessThan', 'downloadable_files');
    acf.registerConditionForFieldType('greaterThan', 'downloadable_files');
	
    var Field = acf.models.UploadFilesField.extend({		   
		type: 'product_images',
	});
	acf.registerFieldType( Field );
	// register existing conditions
	acf.registerConditionForFieldType('hasValue', 'product_images');
	acf.registerConditionForFieldType('hasNoValue', 'product_images');
	acf.registerConditionForFieldType('selectionLessThan', 'product_images');
	acf.registerConditionForFieldType('selectionGreaterThan', 'product_images');

	// state
	var preference = new acf.Model({
		
		name: 'this.collapsedRows',
		
		key: function( key, context ){
			
			// vars
			var count = this.get(key+context) || 0;
			
			// update
			count++;
			this.set(key+context, count, true);
			
			// modify fieldKey
			if( count > 1 ) {
				key += '-' + count;
			}
			
			// return
			return key;
		},
		
		load: function( key ){
			
			// vars 
			var key = this.key(key, 'load');
			var data = acf.getPreference(this.name);
			
			// return
			if( data && data[key] ) {
				return data[key]
			} else {
				return false;
			}
		},
		
		save: function( key, value ){
			
			// vars 
			var key = this.key(key, 'save');
			var data = acf.getPreference(this.name) || {};
			
			// delete
			if( value === null ) {
				delete data[ key ];
			
			// append
			} else {
				data[ key ] = value;
			}
			
			// allow null
			if( $.isEmptyObject(data) ) {
				data = null;
			}
			
			// save
			acf.setPreference(this.name, data);
		}
	});

        var tfFields = ['manage_stock','sold_individually','is_virtual','is_downloadable', 'product_enable_reviews'];

        $.each(tfFields, function(index, value){
            var Field = acf.models.TrueFalseField.extend({		   
                type: value,
            });
            acf.registerFieldType( Field );	
            acf.registerConditionForFieldType('equalTo', value);
            acf.registerConditionForFieldType('notEqualTo', value);
        });
       

    var Field = acf.Field.extend({
		type: 'form_step',
		wait: '',
		events: {
			'click .change-step': 'onClickChangeStep',
		},

        $control: function(){
            return this.$('.frontend-admin-steps');
        },
        $currentStep: function(){
            var step = this.$control().data('current-step');
            if( ! step ) step = 1;
            return step;
        },
        $validateSteps: function(){
            return this.$control().data('validate-steps');
        },
        onClickChangeStep: function( e, $el ){
            var step = $el.data("step");
            var button = $el.data('button');
            var currentStep = this.$currentStep();
            if(step==currentStep) return false;

            if( step == 'submit' || ( this.$validateSteps() && button == 'next' ) ){

                this.$('input.step-input').val(1);
                this.$('.acf-loading').removeClass('acf-hidden');
                this.$('.button').addClass('disabled');
                var field = this;
                var limit = false;
                if( step != 'submit' ){
                    limit = field.$('.acf-fields[data-step='+currentStep+']');
                }

                args = {
                    form: field.$el.parents('form'),
                    reset: false,
                    limit: limit,
                    complete: function( $form, $validator ){
                        if( $validator.hasErrors() ){
                            var first = $validator.data.errors[0];
                            var subField = $form.find('input[name="'+first.input+'"]').closest('.acf-field');
                            var firstErrorOn = subField.closest('.acf-fields').data('step');
                            if( firstErrorOn < currentStep ){
                                field.changeStep(firstErrorOn,currentStep);
                            }
                            field.$el.find('.acf-loading').addClass('acf-hidden');
                            field.$el.find('.disabled').removeClass('disabled');
                            $validator.reset();
                            return;
                        } 
                        if( step == 'submit' ){
                            acf.submitFrontendForm( $form, false );
                        }else{
                            field.changeStep( step, currentStep );
                            $validator.reset();
                        }

                        

                    },
                }
                acf.validateFrontendForm(args);
            }else{
                this.changeStep( step, currentStep );
            }
        },
      
        changeStep: function(step,currentStep){
            this.$('input.step-input').val(step);
            this.$('.form-tab[data-step='+step+']').addClass('active');
            this.$('.form-tab[data-step='+currentStep+']').removeClass('active');
            this.$control().data('current-step',step);
            this.$('.current-step').text(step);
            this.$('.acf-fields[data-step='+currentStep+']').addClass('frontend-admin-hidden');
            this.$('.acf-fields[data-step='+step+']').removeClass('frontend-admin-hidden');
            $('body, html').animate({scrollTop:this.$control().offset().top-100}, 'slow');

            this.$('.acf-loading').addClass('acf-hidden');
            this.$('.disabled').removeClass('disabled');
        }
    });

   
    acf.registerFieldType(Field);

})(jQuery);

