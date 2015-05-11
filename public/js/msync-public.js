(function( $ ) {
  $(document).ready(function () {
    var msync_form = $('form.msync-form');

    msync_form.submit(function(){
      var form = $(this);
      var msync_submit = form.find('.msync-submit');
      var submit_label = msync_submit.val();
      msync_submit.val('Please wait...');
      msync_submit.attr('disabled', 'disabled');
      var formdata = form.serialize();
      formdata = 'action=submit_msync_form&'+ formdata;
      $.ajax({
        url: ajaxurl,
        type: 'post',
        data: formdata,
        dataType: 'json',
        success: function(data, textStatus, XMLHttpRequest) {
          if (data.result == 'success') {
            if (data.redirect != '')
              window.location.replace(data.redirect);
          } else if (data.result == 'error') {
            $.each(data.errors, function(index, value){
              var field_wrapper = form.find('.msync-form-field-' + index);
              field_wrapper.find('.validation').html('');
              var field_error = $("<p/>", {style: 'font-size: 70%; color: red;', html: value});
              field_wrapper.find('.validation').append(field_error);
              // console.log('field ' + index + ': ' + value);
            });
          }
          msync_submit.val(submit_label);
          msync_submit.removeAttr('disabled');
        }
      });
      return false;
    });
  });
})( jQuery );
