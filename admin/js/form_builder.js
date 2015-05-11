(function ($) {
    $(document).ready(function () {
      var marketoFormFields = $('.marketo-list-field');
      var marketoFormCanvas = $('#marketo-form-canvas .form-canvas');
      var marketoFieldSettings = $('#mkto-field-settings');
      var followup = $('#followup');

      marketoFormFields.draggable({
        appendTo: "#marketo-form-canvas .form-canvas",
        helper: "clone",
        revert: "invalid",
        cursor: "move",
      });

      marketoFormCanvas.droppable({
        accept: ".marketo-list-field",
        hoverClass: "active",
        drop: function(event, ui) {
          var field_instance = $(ui.draggable).clone();
          var data = {
            'action' : 'msync_create_field',
            'field_instance_id' : field_instance.attr('data-field-instance-id'),
          }
          $.post(ajaxurl, data, function(response) {
            marketoFormCanvas.append(response);
          });
          $(ui.draggable).addClass('done').draggable('disable');
        },
      }).sortable ({
        containment: '#marketo-form-canvas .form-canvas',
        cursor: 'move',
      });

      function mkto_field_events() {
        $(marketoFormCanvas).on("click", '.delete_control', function() {
          $(this).parent('.mkto_field').remove();
          $('li.marketo-list-field-' + $(this).attr('data-field-id')).removeClass('done').draggable('enable');
        });

        $(marketoFormCanvas).on("click", '.mkto_field',  function() {
          $('.mkto_field').removeClass('active-field').removeClass('field-updated');
          $(this).addClass('active-field');
          var field_instance_id = $(this).attr('data-field-instance-id');
          var data = {
            'action' : 'marketo_form_builder',
            'field_id' : $(this).attr('data-field-id'),
            'field_instance_id' : field_instance_id,
          }
          $.post(ajaxurl, data, function(response) {
            marketoFieldSettings.addClass('marketoFieldSettings').attr('data-field-instance-id', field_instance_id);
            marketoFieldSettings.html(response);
          });
        });

        $(marketoFieldSettings).on("click", 'button', function() {
          var field_instance_id = marketoFieldSettings.attr('data-field-instance-id');
          var field_attributes = ['label', 'required', 'type', 'validation', 'default', 'placeholder', 'instruction', 'label-position'];
          $.each(field_attributes, function(index, field_attribute){
            var settingField = marketoFieldSettings.find('#mkto-field-' + field_attribute);
            var formField = marketoFormCanvas.find('#mkto_field-' + field_instance_id + '-' + field_attribute);
            if (field_attribute == 'required') {
              if((settingField.is(':checked'))) {
                $('.mkto_field-' + field_instance_id).addClass('required');
                value = 1;
              } else {
                value = 0;
                $('.mkto_field-' + field_instance_id).removeClass('required');
              }
            } else {
              var value = settingField.val();
            }
            console.log(value);

            formField.val(value);
          });

          var mkto_field = marketoFormCanvas.find('.mkto_field-' + field_instance_id);
          mkto_field.addClass('field-updated');
        })

        $(followup).on("change", '#form-followup-type', function(){
          console.log($(this).val());
          followup.find('.followup-wrapper').removeClass('active');
          switch($(this).val()) {
            case 'url':
              followup.find('.followup-url-wrapper').addClass('active');
              break;
            case 'post':
              followup.find('.followup-post-wrapper').addClass('active');
              break;
          }
        });
      }
      mkto_field_events();
    });
})(jQuery);
