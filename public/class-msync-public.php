<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://magiclogix.com
 * @since      1.0.0
 *
 * @package    mSync
 * @subpackage mSync/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    mSync
 * @subpackage mSync/public
 * @author     Sam Timalsina <stimalsina@magiclogix.com>
 */
class MSync_Public {

  /**
   * The ID of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $plugin_name    The ID of this plugin.
   */
  private $plugin_name;

  /**
   * The version of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   * @param      string    $plugin_name       The name of the plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct( $plugin_name, $version ) {

    $this->plugin_name = $plugin_name;
    $this->version = $version;

  }

  /**
   * Register the stylesheets for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_styles() {
    wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/msync-public.css', array(), $this->version, 'all' );
  }

  /**
   * Register the scripts for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_scripts() {
    wp_enqueue_script($this->plugin_name . '-public', plugin_dir_url( __FILE__ ) . 'js/msync-public.js', array( 'jquery' ), $this->version, false );
  }

  public function munchikin_tracking() {
    $settings = get_option('msync', array());
    if(isset($settings['msync_tracking_code']) && isset($settings['msync_munchkin_id'])) {
      switch ($settings['msync_tracking_code']) {
        case 'simple':
          echo "<script type=\"text/javascript\">
            document.write(unescape(\"%3Cscript src='//munchkin.marketo.net/munchkin.js' type='text/javascript'%3E%3C/script%3E\"));
          </script>
          <script>Munchkin.init('" . $settings['msync_munchkin_id'] . "');</script>";
          break;

        case 'async':
          echo "<script type=\"text/javascript\">
            (function() {
              var didInit = false;
              function initMunchkin() {
                if(didInit === false) {
                  didInit = true;
                  Munchkin.init('" . $settings['msync_munchkin_id'] . "');
                }
              }
              var s = document.createElement('script');
              s.type = 'text/javascript';
              s.async = true;
              s.src = '//munchkin.marketo.net/munchkin.js';
              s.onreadystatechange = function() {
                if (this.readyState == 'complete' || this.readyState == 'loaded') {
                  initMunchkin();
                }
              };
              s.onload = initMunchkin;
              document.getElementsByTagName('head')[0].appendChild(s);
            })();
          </script>";
          break;

        case 'jquery':
          $inline_js =  "<script type=\"text/javascript\">
            $.ajax({
              url: '//munchkin.marketo.net/munchkin.js',
              dataType: 'script',
              cache: true,
              success: function() {
                Munchkin.init('" . $settings['msync_munchkin_id'] . "');
              }
            });
          </script>";
          $this->enqueue_inline_script("inline-marketo", $inline_js, array('jquery'));
          break;
        default:
          break;
      }
    }
  }

  public function enqueue_inline_script( $handle, $js, $deps = array(), $in_footer = false ){
    // Callback for printing inline script.
    $cb = function()use( $handle, $js ) {
      // Ensure script is only included once.
      if(wp_script_is( $handle, 'done'))
      return;
      // Print script & mark it as included.
      echo $js;
      global $wp_scripts;
      $wp_scripts->done[] = $handle;
    };

    $hook = $in_footer ? 'wp_print_footer_scripts' : 'wp_print_scripts';

    // If no dependencies, simply hook into header or footer.
    if( empty($deps)){
      add_action( $hook, $cb);
      return;
    }

    // Delay printing script until all dependencies have been included.
    $cb_maybe = function()use( $deps, $in_footer, $cb, &$cb_maybe ) {
      foreach( $deps as &$dep ) {
        if( !wp_script_is( $dep, 'done' )) {
          // Dependencies not included in head, try again in footer.
          if(!$in_footer ){
            add_action( 'wp_print_footer_scripts', $cb_maybe, 11 );
          }
          return;
        }
      }
    call_user_func($cb);
    };
    add_action( $hook, $cb_maybe, 0 );
  }

  public static function get_forms() {

  }

  public static function render_form($form_data) {
    if(!is_array($form_data)) return;
    $extra_class = 'widget';
    require(dirname(__FILE__) . '/partials/form.php');
  }

  public function ajax_submit_form() {
    $payload = $_POST['marketo-field'];
    $form_validated = TRUE;
    foreach ($payload as $key => $value) {
      $field_validate = self::field_validate($key, $value);
      if (!$field_validate['validate']) {
        $validation_errors[$key] = $field_validate['error'];
        $form_validated = FALSE;
      } else {
        $validation_errors[$key] = FALSE;
      }
    }
    if ($form_validated) {
      MSync_Public::push_lead($_POST['marketo-field']);
      // switch($form->followup_type) {
      //   case 'post':
      //     $followup_url = get_post_permalink($form->followup_destination);
      //     break;
      //   case 'url':
      //     $followup_url = $form->followup_destination;
      //     break;
      // }
      // wp_redirect($followup_url, 301);
      // exit;
      echo json_encode(array('result' => 'success', 'redirect' => 'http://google.com'));
    } else {
      echo json_encode(array('result' => 'error', 'errors' => $validation_errors));
    }
    exit();
  }

  public static function field_validate ($field_id, $input) {
    $field = self::get_field_validation_type($field_id);
    $validation_type = $field->validation;
    if($field->is_required == 1 && empty($validation_type)) {
      $validation_type = 'required';
    }
    if(empty($validation_type)) return array('validate' => true);
    switch ($validation_type) {
      case 'number':
        $pattern = '/^-?(?:\d+(?:\.\d+)?|\.\d+)$/i';
        $error = 'Not a number';
        break;
      case 'email':
        $pattern = '/^\w(?:\.?[\w%+-]+)*@\w(?:[\w-]*\.)+?[a-z]{2,}$/i';
        $error = 'Not a valid email';
        break;
      case 'uri':
        $pattern = '/^[^\s:\/?#]+:(?:\/{2,3})?[^\s.\/?#]+(?:\.[^\s.\/?#]+)*(?:\/?[^\s?#]*\??[^\s?#]*(#[^\s#]*)?)?$/';
        $error = 'Not a valid web address';
        break;
      case 'required':
        if($input === '') {
          return array('validate' => false, 'error' => 'This field is required');
        } else {
          return array('validate' => true);
        }
    }
    if(!preg_match($pattern, $input)) {
      return array('validate' => false, 'error' => $error);
    } else {
      return array('validate' => true);
    }
  }

  public static function get_field_validation_type($field_id) {
    global $wpdb;
    $fields_table = $wpdb->prefix . MSync::$form_fields_table;
    $field_data = $wpdb->get_row('SELECT is_required, validation FROM `' . $fields_table . '` WHERE field_id = ' . $field_id);
    return $field_data;
  }

  public static function push_lead($data) {
    $lead = array();
    foreach($data as $key => $value) {
      $keyName = self::get_field_name($key);
      if($keyName)
        $lead[$keyName] = $value;
    }
    MSync::$marketo_client->push($lead);
  }

  public static function get_field_name($key) {
    global $wpdb;
    $fields_table = $wpdb->prefix . MSync::$form_fields_table;
    $field_list_table = $wpdb->prefix . MSync::$field_list_table;
    $field_data = $wpdb->get_row('SELECT fl.rest_name FROM `' . $field_list_table . '` AS fl LEFT JOIN `' . $fields_table . '` AS ft ON ft.field_instance_id = fl.id WHERE field_id = ' . $key);
    return $field_data->rest_name;
  }

  public function msync_form_append_to_page($content) {
    global $post;
    $form_ids = $this->get_form_ids_by_post_id($post->ID);
    $extra_class = 'meta';
    if(!is_admin() && is_main_query() && ( is_page() OR is_single() ) ){
      $form = '';
      if(is_array($form_ids) && !empty($form_ids)){
        foreach($form_ids as $form_id){
          $form_data = MSync::get_form($form_id);
          if(is_array($form_data)) {
            require(dirname(__FILE__) . '/partials/form.php');
          }
        }
      } else {
        $form_id = $form_ids;
        $form_data = MSync::get_form($form_ids);
        if(is_array($form_data)) {
          require(dirname(__FILE__) . '/partials/form.php');
        }
      }
    }
    return $content;
  }

  public function get_form_ids_by_post_id($post_id) {
    global $wpdb;
    $form_ids = array();
    if(is_page($post_id) ){
      $form_id = get_post_meta( $post_id, '_msync_form_id', true );
      if( !empty( $form_id ) ){
        $form_ids[] = $form_id;
      }
    } else if(is_single( $post_id ) ){
      $form_id = get_post_meta( $post_id, '_msync_form_id', true );
      if( !empty( $form_id ) ){
        $form_ids[] = $form_id;
      }
    }
    return $form_ids;
  }

  function msync_shortcode($atts) {
    extract(shortcode_atts(array(
      'form' => false,
    ), $atts));
    if($form) {
      $extra_class = 'embed';
      $form_data = MSync::get_form($form);
      ob_start();
      if(is_array($form_data)) {
        require(dirname(__FILE__) . '/partials/form.php');
      }
      return ob_get_clean();
    } else {
      return '';
    }
  }
}
