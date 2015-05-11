/**
 * Shortcodes
 *
 * @author Magic Logix
 */
(function($){
  $(document).ready(function () {
    var $admin;

    function send(content) {
      var win = window.dialogArguments || opener || parent || top;
      win.send_to_editor(content);
      console.log('shortcode sent');
    }

    function buildShortcode(container) {
      var formId = container.find('[name="marketo_form_id"]').val();
      console.log('shortcode ready to send');
      return '[marketo form="' + formId + '"]';
    }

    function addForm(e) {
      console.log('button clicked');
      var target = $(e.currentTarget),
        container = target.closest('#TB_ajaxContent');

      send(buildShortcode(container));
    }

    $admin = $('body.wp-admin');
    if ($admin.length > 0) {
      $admin.on('click', '#msync-add-shortcode-button', addForm);
    }
  });
})(jQuery);
