(function($) {

  af.calculated = {

    initialize: function( form ) {
      // Find all calculated fields and set them up
      $calculated_fields = form.$el.find( '.acf-field-calculated' );

      $calculated_fields.each(function( i, field ) {
        af.calculated.setupField( form, $(field) );
      })
    },

    setupField: function( form, $field ) {
      var self = this;
      var name = $field.attr( 'data-name' );
      var key = $field.attr( 'data-key' );

      var refreshHandler = function() {
        self.refreshField( form, $field );
      };

      // Perform an initial refresh to populate the field with empty data
      refreshHandler();

      // Listen for form changes and refresh the field
      form.$el.change( refreshHandler );

      // Allow triggering of a refresh through an action
      acf.addAction( 'af/field/calculated/update_value', refreshHandler );
      acf.addAction( 'af/field/calculated/update_value/name=' + name, refreshHandler );
      acf.addAction( 'af/field/calculated/update_value/key=' + key, refreshHandler );
    },

    refreshField: function( form, $field ) {
      var self = this;

      // Prepare AJAX request with field key and serialized form data
      var key = $field.attr( 'data-key' );
      var data = acf.serialize( form.$el );

      data.action = 'af_calculated_field';
      data.calculated_field = key;

      data = acf.prepare_for_ajax( data );

      // Lock field to indicate loading
      self.lockField( $field );

      // Fetch updated field value through AJAX
      $.ajax({
        url: acf.get('ajaxurl'),
        data: data,
        type: 'post',
        success: function( data ){
          // Update field contents
          self.updateField( form, $field, data );
        },
        complete: function(){
          // Unlock field again once the request has finished (successfully or not)
          self.unlockField( $field );
        }
      });
    },

    updateField: function( form, $field, value ) {
      $field.find( 'input.af-calculated-value' ).val( value );
      $field.find( '.af-calculated-content' ).html( value );

      var name = $field.attr( 'data-name' );
      var key = $field.attr( 'data-key' );

      acf.doAction( 'af/field/calculated/value_updated', value, $field, form );
      acf.doAction( 'af/field/calculated/value_updated/name=' + name, value, $field, form );
      acf.doAction( 'af/field/calculated/value_updated/key=' + key, value, $field, form );
    },

    lockField: function( $field ) {
      $field.find( '.af-input' ).css( 'opacity', 0.5 );
    },

    unlockField: function( $field ) {
      $field.find( '.af-input' ).css( 'opacity', 1.0 );
    },

  };

  af.recaptcha = {
    initialize: function( form ) {
      var site_key = af.recaptcha.getSiteKey( form );
      if (site_key === null) {
        return;
      }

      var $container = $('<div class="af-recaptcha-container">').attr('data-size', 'invisible');
      var $submit_wrapper = form.$el.find('.af-submit');
      $submit_wrapper.append( $container );

      // Add a submission step to perform a reCAPTCHA check.
      // A low priority is used to ensure reCAPTCHA runs early (before AJAX)
      af.addSubmissionStep( form, 5, function( callback ) {
        // There is no way of detecting when reCAPTCHA has been closed.
        // Instead we unlock the form after a two seconds in case the user tries again.
        unlockFormTimeout = setTimeout(function() {
          acf.unlockForm( form.$el );
        }, 2000);

        // Triggered after a successful captcha check.
        // Adds the token to a hidden field and continues the submission process.
        var captchaCallback = function(token) {
          // Ensure the form is locked after the captcha succeeds to avoid duplicate submissions
          clearTimeout( unlockFormTimeout );
          acf.lockForm( form.$el );

          var $token_input = $( '<input type="hidden" name="g-recaptcha-response" />' ).val( token );
          form.$el.find( '.acf-hidden' ).append( $token_input );
          callback();
        };

        var recaptcha_widget_id = grecaptcha.render(
          $container.get(0),
          {
            'sitekey': site_key,
            callback: captchaCallback,
          }
        );

        grecaptcha.execute( recaptcha_widget_id );
      });
    },

    getSiteKey: function( form ) {
      var site_key = form.$el.attr( 'data-recaptcha-site-key' );
      if (typeof site_key !== typeof undefined && site_key !== false) {
        return site_key;
      } else {
        return null;
      }
    },
  };

  acf.addAction( 'af/form/setup', af.calculated.initialize );
  acf.addAction( 'af/form/setup', af.recaptcha.initialize );


  // Add post ID to ACF AJAX requests when editing a post
  af.addPostID = function( data ) {
    // Check if data has field key
    if ( ! data.hasOwnProperty( 'field_key' ) ) {
      return data;
    }

    // Find field with key
    var key = data.field_key;
    var $field = $('.af-field[data-key="' + key + '"]');
    if ( ! $field.length ) {
      return data;
    }

    var $post_id_input = $field.siblings( '.acf-hidden' ).find( 'input[name="post_id"]' );
    if ( $post_id_input.length ) {
      data.post_id = $post_id_input.val();
    }

    return data;
  };

  acf.addFilter( 'prepare_for_ajax', af.addPostID );

})(jQuery);