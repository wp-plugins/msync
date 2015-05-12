<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://magiclogix.com
 * @since      1.0.0
 *
 * @package    mSync
 * @subpackage mSync/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    mSync
 * @subpackage mSync/admin
 * @author     Sam Timalsina <stimalsina@magiclogix.com>
 */
class MSync_Admin {

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
   * @param      string    $plugin_name       The name of this plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct( $plugin_name, $version ) {

    $this->plugin_name = $plugin_name;
    $this->version = $version;

  }

  /**
   * Register the stylesheets for the admin area.
   *
   * @since    1.0.0
   */
  public function enqueue_styles() {

    /**
     *
     */

    wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/msync-admin.css', array(), $this->version, 'all' );

  }

  /**
   * Register the JavaScript for the admin area.
   *
   * @since    1.0.0
   */
  public function enqueue_scripts() {

    /**
     *
     */

    wp_enqueue_script($this->plugin_name . 'admin', plugin_dir_url( __FILE__ ) . 'js/msync-admin.js', array( 'jquery' ), $this->version, false );
    wp_enqueue_script($this->plugin_name . 'form_builder', plugin_dir_url( __FILE__ ) . 'js/form_builder.js', array('jquery-ui-sortable', 'jquery-ui-droppable', 'jquery'));

  }

  public function setup_admin_menu() {
    add_options_page('mSync Settings', 'mSync Settings', 'manage_options', 'msync_settings', array(&$this, 'display_admin_form'));
    add_menu_page('All Forms', 'mSync', 'manage_options', 'msync', array(&$this, 'display_msync_forms'), 'dashicons-welcome-widgets-menus', 91 );
    add_submenu_page('msync', 'mSync: All Forms', 'All Forms', 'manage_options', 'msync', array(&$this, 'display_msync_forms'));
    add_submenu_page('msync', 'mSync: Add New', 'Add New', 'manage_options', 'builder', array(&$this, 'display_msync_form_builder'));
    add_submenu_page('msync', 'mSync: How it Works', 'About', 'manage_options', 'about', array(&$this, 'display_msync_about'));
  }

  public function display_admin_form() {
    extract($this->get_settings());
    $plugin_ready = $this->is_plugin_ready();
    require(dirname( __FILE__ ) . '/partials/msync-admin-settings-form.php');
  }

  public function display_msync_about() {
    require(dirname( __FILE__ ) . '/partials/msync-about.php');
  }

  /**
   * Helper to get the settings
   *
   * @param  string $key
   * @return mixed
   */
  public function get_settings($key = null) {
    $defaults = array(
      'msync_munchkin_id' => '',
      'msync_client_id' => '',
      'msync_client_secret' => '',
      'msync_tracking_code' => '',
    );

    $settings = get_option('msync', array());
    // echo "<pre>"; print_r($settings); echo "</pre>";
    $settings = wp_parse_args($settings, $defaults);
    if($key) {
      $settings = isset($settings[$key]) && ! empty($settings[$key]) ? $settings[$key] : false ;
    }
    return $settings;
  }

  /**
   * Save settings on a msync_settings_save action
   *
   * @return void
   */
  public function save_settings() {
    if(!isset($_REQUEST['msync_settings_save']) ) return;

    // Die if not post
    if($_SERVER['REQUEST_METHOD'] !== 'POST') wp_die('This request must be sent with a post request');
    // Die if can't manage_options
    if( ! current_user_can('manage_options') ) wp_die('you do not have authorization to view this page');

    if(isset($_REQUEST['sync-fields']) && $_REQUEST['sync-fields'] = 'Sync Fields') {
      $sync_result = $this->msync_sync_fields();
      if($sync_result) {
        add_settings_error('msync_settings', 'msync_notices', __($sync_result . ' fields synced successfully.'), 'updated');
      } else {
        add_settings_error('msync_settings', 'msync_notices', __('Could not sync fields. Please check your Marketo Credentials'), 'error');
      }
    } else if(isset($_REQUEST['test-connection']) && $_REQUEST['test-connection'] = 'Test Connection') {
      if ($this->msync_test_connection()) {
        add_settings_error('msync_settings', 'msync_notices', __('Connection is working :)'), 'updated');
      } else {
        add_settings_error('msync_settings', 'msync_notices', __('Could not connect to Marketo. Please check your Credentials'), 'error');
      }
    } else {
      update_option('msync', $_REQUEST['msync']);
      /**
       * Handle settings errors and return to options page
       */
      // If no settings errors were registered add a general 'updated' message.
      add_settings_error('msync_settings', 'msync_notices', __("Marketo Credentials saved"), 'updated');
    }
    set_transient('settings_errors', get_settings_errors(), 30);

    $goback = add_query_arg('settings-updated', 'true', wp_get_referer());
    wp_redirect( $goback );
    exit;
  }

  public function msync_notices_action() {
    settings_errors('msync_notices');
  }

  public function msync_activation_notice() {
    if (!$this->is_plugin_ready()) {
      global $pagenow;
      if ($pagenow == 'plugins.php') {
        require(dirname( __FILE__ ) . '/partials/activation-notice.php');
      }
    }
  }

  public function is_plugin_ready() {
    extract($this->get_settings());
    if (empty($msync_munchkin_id) || empty($msync_client_id) || empty($msync_client_secret)) {
      return false;
    }
    return true;
  }

  private function msync_sync_fields() {
    $fields = MSync::$marketo_client->describe();
    if(is_array($fields)) {
      global $wpdb;
      $table_name = $wpdb->prefix . MSync::$field_list_table;
      $count = 0;
      foreach ($fields as $field):
        $wpdb->replace(
          $table_name,
          array(
            'id' => $field->id,
            'display_name' => $field->displayName,
            'rest_name' => $field->rest->name,
            'data_type' => $field->dataType,
            'rest_readonly' => $field->rest->readOnly,
          )
        );
        $count++;
      endforeach;
      return $count;
    }
    return false;
  }

  private function get_lead_fields() {
    global $wpdb;
    $rendered_fields = '';
    $fields = $wpdb->get_results('SELECT id, display_name, rest_name, data_type FROM `' . $wpdb->prefix . 'jmerge_field_list` WHERE rest_readonly=0');
    if(is_array($fields)) {
      foreach ($fields as $field):
        $type = '';
        switch ($field->data_type) {
          case 'string':
            $type = 'dashicons-editor-textcolor';
            break;
          case 'text':
            $type = 'dashicons-editor-spellcheck';
            break;
          case 'url':
            $type = 'dashicons-admin-links';
            break;
          case 'currency':
            $type = 'dashicons-chart-bar';
            break;
          case 'integer':
            $type = 'dashicons-editor-ol';
            break;
          case 'float':
            $type = 'dashicons-dashboard';
            break;
          case 'email':
            $type = 'dashicons-email-alt';
            break;
          case 'phone':
            $type = 'dashicons-phone';
            break;
          case 'date':
            $type = 'dashicons-calendar';
            break;
          case 'datetime':
            $type = 'dashicons-calendar-alt';
            break;
          case 'boolean':
            $type = 'dashicons-admin-settings';
            break;
          case 'reference':
            $type = 'dashicons-networking';
            break;
        }
        $rendered_fields .= '<li class="marketo-list-field marketo-list-field-' . $field->id . '" data-value="' . $field->rest_name . '" data-type="' . $field->data_type . '" data-field-instance-id="' . $field->id . '" data-display-name="' . $field->display_name . '">';
        $rendered_fields .= '<span class="marketo-field dashicons ' . $field->data_type . ' ' . $type . '"></span>';
        $rendered_fields .= '<span>' . $field->display_name . ':</span></li>';
      endforeach;
    }
    return $rendered_fields;
  }

  public function msync_test_connection() {
    return MSync::$marketo_client->testConnection();
  }

  public function display_msync_forms() {
    require_once(dirname( __FILE__ ) . '/class-msync-table.php');
    require(dirname( __FILE__ ) . '/partials/msync-admin-list-forms.php');
  }

  public function display_msync_form_builder() {
    $msync_lead_fields = $this->get_lead_fields();
    $form = isset($_POST['form']) ? $_POST['form'] : false;
    if($form) {
      $form_fields = isset($_POST['mkto_field']) ? $_POST['mkto_field'] : false;
      if($this->form_validate($form, $form_fields)) {
        if(isset($form['id'])) {
          $this->update_form($form, $form_fields);
        } else {
          $form_id = $this->add_form($form, $form_fields);
          $goback = add_query_arg('form_id', $form_id,  wp_get_referer());
          print('<script>window.location.href="' . $goback . '"</script>');
        }
      }
    }
    if(isset($_REQUEST['form_id'])) {
      $form_data = $this->get_form($_REQUEST['form_id']);
    }
    require_once(dirname( __FILE__ ) . '/partials/msync-admin-form-builder.php');
  }

  private function form_validate($form, $fields) {
    if(empty($form['title'])) {
      self::msync_notice('Did you forget the form title?', 'error');
      return false;
    }
    if(empty($fields)) {
      self::msync_notice('You need at least one field', 'error');
      return false;
    }
    return true;
  }

  public function generate_field_settings() {
    $field_id = isset($_POST['field_id']) ? $_POST['field_id'] : false;
    $field_instance_id = isset($_POST['field_instance_id']) ? $_POST['field_instance_id'] : false;
    if($field_id) {
      $field = MSync::get_field($field_id);
      require_once(dirname(__FILE__) . '/partials/field-settings.php');
    } else if($field_instance_id) {
      $field_instance = MSync::get_field_instance($field_instance_id);
      $field = $this->get_field_from_instance($field_instance);
      require_once(dirname(__FILE__) . '/partials/field-settings.php');
    }
    die();
  }

  public function create_field() {
    $field_instance_id = $_POST['field_instance_id'];
    $field_instance = MSync::get_field_instance($field_instance_id);
    $field_instance->instruction = '';
    $field_instance->label_position = 'above  ';
    require_once(dirname( __FILE__ ) . '/partials/field-instance.php');
    die();
  }

  public function get_field_from_instance($field_instance) {
    $field = new stdClass();
    $field->label = $field_instance->display_name;
    $field->input_type = $this->get_inputtype_from_datatype($field_instance->data_type);
    $field->is_required = 1;
    $field->validation = '';
    $field->default = '';
    $field->placeholder = $field_instance->display_name;
    $field->instruction = '';
    $field->label_position = 'above';
    return $field;
  }

  public function get_inputtype_from_datatype($data_type) {
    $field_type = '';
    switch ($data_type) {
      case 'string': default:
        $field_type = 'text';
        break;
      case 'boolean':
        $field_type = 'radio';
        break;
      case 'text':
        $field_type = 'textarea';
        break;
    }
    return $field_type;
  }

  public function add_form($form, $fields) {
    global $wpdb;
    $form_table = $wpdb->prefix . MSync::$forms_table;
    $followup_destination = ($form['followup-type'] == 'url') ? $form['followup-url'] : $form['followup-post'];
    $wpdb->insert(
      $form_table,
      array (
        'form_name' => $form['title'],
        'form_description' => $form['description'],
        'button_label' => $form['button_label'],
        'followup_type' => $form['followup-type'],
        'followup_destination' => $followup_destination,
      )
    );
    $form_id = $wpdb->insert_id;
    if(is_array($fields)) {
      $sort_position = 0;
      $fields_table = $wpdb->prefix . MSync::$form_fields_table;
      foreach ($fields as $field_id => $field):
        $wpdb->replace(
          $fields_table,
          array(
            'field_instance_id' => $field_id,
            'form_id' => $form_id,
            'label' => $field['label'],
            'input_type' => $field['type'],
            'validation' => $field['validation'],
            'is_required' => $field['required'],
            'default' => $field['default'],
            'placeholder' => $field['placeholder'],
            'sort_position' => $sort_position,
            'instruction' => $field['instruction'],
            'label_position' => $field['label_position'],
            'active' => 1,
          )
        );
      $sort_position++;
      endforeach;
    }
    return $form_id;
  }

  public function update_form($form, $fields) {
    global $wpdb;
    $form_table = $wpdb->prefix . MSync::$forms_table;
    $followup_destination = ($form['followup-type'] == 'url') ? $form['followup-url'] : $form['followup-post'];
    $wpdb->update(
      $form_table,
      array (
        'form_name' => $form['title'],
        'form_description' => $form['description'],
        'button_label' => $form['button_label'],
        'followup_type' => $form['followup-type'],
        'followup_destination' => $followup_destination,
      ),
      array (
        'id' => $form['id'],
      )
    );
    $fields_table = $wpdb->prefix . MSync::$form_fields_table;
    //First Make all fields not active.
    $wpdb->update( $fields_table, array('active' => 0), array('form_id' => $form['id']));
    if(is_array($fields)) {
      $sort_position = 0;
      foreach ($fields as $field_instance_id => $field):
        $wpdb->replace(
          $fields_table,
          array(
            'field_id' => isset($field['field_id']) ? $field['field_id'] : '',
            'field_instance_id' => $field_instance_id,
            'form_id' => $form['id'],
            'label' => $field['label'],
            'input_type' => $field['type'],
            'validation' => $field['validation'],
            'is_required' => $field['required'],
            'default' => $field['default'],
            'placeholder' => $field['placeholder'],
            'sort_position' => $sort_position,
            'instruction' => $field['instruction'],
            'label_position' => $field['label_position'],
            'active' => 1,
          )
        );
        $sort_position++;
      endforeach;
    }
    $wpdb->delete($fields_table, array('active' => 0, 'form_id' => $form['id']));
  }

  public function get_form($form_id) {
    $form = MSync::get_form($form_id);
    return $form;
  }

  /**
   * Adds the meta box container.
   */
  public function add_meta_box( $post_type ) {
    $post_types = array('post', 'page');     //limit meta box to certain post types
    if ( in_array( $post_type, $post_types )) {
      add_meta_box(
        'msync'
        ,__( 'Append a Marketo Form', 'msync_textdomain' )
        ,array( $this, 'render_meta_box_content' )
        ,$post_type
        ,'side'
        ,'default'
      );
    }
  }



  /**
   * Save the meta when the post is saved.
   *
   * @param int $post_id The ID of the post being saved.
   */
  public function save( $post_id ) {

    /*
     * We need to verify this came from the our screen and with proper authorization,
     * because save_post can be triggered at other times.
     */

    // Check if our nonce is set.
    if ( ! isset( $_POST['msync_nonce'] ) )
      return $post_id;

    $nonce = $_POST['msync_nonce'];

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $nonce, 'msync_custom_box' ) )
      return $post_id;

    // If this is an autosave, our form has not been submitted,
                //     so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
      return $post_id;

    // Check the user's permissions.
    if ( 'page' == $_POST['post_type'] ) {

      if ( ! current_user_can( 'edit_page', $post_id ) )
        return $post_id;

    } else {

      if ( ! current_user_can( 'edit_post', $post_id ) )
        return $post_id;
    }

    /* OK, its safe for us to save the data now. */

    // Sanitize the user input.
    $mydata = sanitize_text_field( $_POST['msync_form_id'] );

    // Update the meta field.
    update_post_meta( $post_id, '_msync_form_id', $mydata );
  }

  /**
   * Render Meta Box content.
   *
   * @param WP_Post $post The post object.
   */
  public function render_meta_box_content($post) {
    // Add an nonce field so we can check for it later.
    wp_nonce_field( 'msync_custom_box', 'msync_nonce' );

    // Use get_post_meta to retrieve an existing value from the database.
    $value = get_post_meta( $post->ID, '_msync_form_id', true );

    // Display the form, using the current value.
    echo '<label for="msync_form_id">';
    _e( 'Marketo Form', 'msync_textdomain' );
    echo '</label> ';?>
    <select id="msync_form_id" name="msync_form_id">
      <option value="0">-- <?php _e('None', 'msync');?></option>
      <?php
      $all_forms = MSync::get_forms();
      foreach($all_forms as $form):
        $title = $form->form_name;
        $id = $form->id;?>
        <option value = "<?php echo $id;?>" <?php selected( $id, esc_attr($value));?>>
          <?php echo $title;?>
        </option>
      <?php endforeach;?>
    </select>
    <?php
  }

  /**
   * Add the button to the wysiwyg
   *
   * @param  string $context
   * @return string
   */
  public function insert_form_button($context) {
    $icon = '<span ';
    $icon .= 'class="wp-media-buttons-icon dashicons dashicons-groups" ';
    $icon .= '></span>';


    $context .= '<a href="#TB_inline?width=400&inlineId=msync_popup_container" ' .
       'id="insert-media-button" ' .
       'class="button thickbox" ' .
       'title="Choose Form" ' .
    '> ' . $icon . ' Add Marketo Form</a>';
    return $context;
  }

  /**
   * Add the button to the wysiwyg
   *
   * @author Magic Logix
   * @param  string $context
   * @return string
   */
  public function shortcode_popup_content() {
    $marketo_forms = MSync::get_forms();
    require_once(dirname(__FILE__) . '/partials/shortcode-form.php');
  }

  public static function msync_notice ($message, $type = 'updated') {
    if (!empty($message)) {
      echo '<div class="' . $type . '">
        <p>' . $message . '</p>
      </div>';
    }
  }
}

