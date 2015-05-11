<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://magiclogix.com
 * @since      1.0.0
 *
 * @package    mSync
 * @subpackage mSync/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    mSync
 * @subpackage mSync/includes
 * @author     Sam Timalsina <stimalsina@magiclogix.com>
 */
class MSync {

  /**
   * The loader that's responsible for maintaining and registering all hooks that power
   * the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      MSync_Loader    $loader    Maintains and registers all hooks for the plugin.
   */
  protected $loader;

  /**
   * The unique identifier of this plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $plugin_name    The string used to uniquely identify this plugin.
   */
  protected $plugin_name;

  /**
   * The current version of the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $version    The current version of the plugin.
   */
  protected $version;
  protected $plugin_admin;
  protected $plugin_widget;

  public static $field_list_table = 'msync_field_list';
  public static $forms_table = 'msync_forms';
  public static $form_fields_table = 'msync_form_fields';

  public static $marketo_client;

  /**
   * Define the core functionality of the plugin.
   *
   * Set the plugin name and the plugin version that can be used throughout the plugin.
   * Load the dependencies, define the locale, and set the hooks for the admin area and
   * the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function __construct() {

    $this->plugin_name = 'msync';
    $this->version = '1.0.0';
    $this->load_dependencies();
    $this->plugin_admin = new MSync_Admin( $this->get_plugin_name(), $this->get_version());
    $this->plugin_widget = new MSync_Widget();
    $this->set_locale();
    $this->define_admin_hooks();
    $this->define_public_hooks();
    $this->set_marketo_client();
  }

  /**
   * Load the required dependencies for this plugin.
   *
   * Include the following files that make up the plugin:
   *
   * - MSync_Loader. Orchestrates the hooks of the plugin.
   * - MSync_i18n. Defines internationalization functionality.
   * - MSync_Admin. Defines all hooks for the admin area.
   * - MSync_Public. Defines all hooks for the public side of the site.
   *
   * Create an instance of the loader which will be used to register the hooks
   * with WordPress.
   *
   * @since    1.0.0
   * @access   private
   */
  private function load_dependencies() {

    /**
     * The class responsible for talking to Marketo
     * core plugin.
     */
    require_once plugin_dir_path( dirname( __FILE__)) . 'includes/class-marketo-api.php';

    /**
     * The class responsible for orchestrating the actions and filters of the
     * core plugin.
     */
    require_once plugin_dir_path( dirname( __FILE__)) . 'includes/class-msync-loader.php';

    /**
     * The class responsible for defining internationalization functionality
     * of the plugin.
     */
    require_once plugin_dir_path( dirname( __FILE__)) . 'includes/class-msync-i18n.php';

    /**
     * The class responsible for defining all actions that occur in the admin area.
     */
    require_once plugin_dir_path( dirname( __FILE__)) . 'admin/class-msync-admin.php';

    /**
     * The class responsible for defining all actions that occur in the public-facing
     * side of the site.
     */
    require_once plugin_dir_path( dirname( __FILE__)) . 'public/class-msync-public.php';

    /**
     * The class responsible for adding widgets
     */
    require_once plugin_dir_path( dirname( __FILE__)) . 'admin/class-msync-widget.php';

    $this->loader = new MSync_Loader();

  }

  /**
   * Define the locale for this plugin for internationalization.
   *
   * Uses the MSync_i18n class in order to set the domain and to register the hook
   * with WordPress.
   *
   * @since    1.0.0
   * @access   private
   */
  private function set_locale() {

    $plugin_i18n = new MSync_i18n();
    $plugin_i18n->set_domain( $this->get_plugin_name());

    $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

  }

  /**
   * Register all of the hooks related to the admin area functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_admin_hooks() {
    $this->loader->add_action('admin_enqueue_scripts', $this->plugin_admin, 'enqueue_styles');
    $this->loader->add_action('admin_enqueue_scripts', $this->plugin_admin, 'enqueue_scripts');
    $this->loader->add_action('admin_menu', $this->plugin_admin, 'setup_admin_menu');
    $this->loader->add_action('admin_init', $this->plugin_admin, 'save_settings');
    $this->loader->add_action('admin_notices', $this->plugin_admin, 'msync_notices_action');
    $this->loader->add_action('admin_notices', $this->plugin_admin, 'msync_notice');
    $this->loader->add_action('widgets_init', $this->plugin_widget, 'register_widget');
    $this->loader->add_action('wp_ajax_marketo_form_builder', $this->plugin_admin, 'generate_field_settings');
    $this->loader->add_action('wp_ajax_msync_create_field', $this->plugin_admin, 'create_field');
    $this->loader->add_action('add_meta_boxes', $this->plugin_admin, 'add_meta_box');
    $this->loader->add_action('media_buttons_context', $this->plugin_admin, 'insert_form_button');
    $this->loader->add_action('admin_footer', $this->plugin_admin, 'shortcode_popup_content');
    $this->loader->add_action('save_post', $this->plugin_admin, 'save');
  }

  /**
   * Register all of the hooks related to the public-facing functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_public_hooks() {

    $plugin_public = new MSync_Public( $this->get_plugin_name(), $this->get_version());
    $this->loader->add_shortcode('marketo', $plugin_public, 'msync_shortcode');
    $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
    $this->loader->add_action('wp_footer', $plugin_public, 'munchikin_tracking');
    $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    $this->loader->add_action('wp_ajax_submit_msync_form', $plugin_public, 'ajax_submit_form');
    $this->loader->add_filter('the_content', $plugin_public, 'msync_form_append_to_page', 999);

  }

  /**
   * Set Marketo Client
   *
   * @since    1.0.0
   * @access   private
   */
  private function set_marketo_client() {
    $settings = $this->plugin_admin->get_settings();
    self::$marketo_client = new MarketoRestAPI(array(
      'client_id' => $settings['msync_client_id'],
      'client_secret' => $settings['msync_client_secret'],
      'munchkin_id' => $settings['msync_munchkin_id']
   ));
  }

  /**
   * Run the loader to execute all of the hooks with WordPress.
   *
   * @since    1.0.0
   */
  public function run() {
    $this->loader->run();
  }

  /**
   * The name of the plugin used to uniquely identify it within the context of
   * WordPress and to define internationalization functionality.
   *
   * @since     1.0.0
   * @return    string    The name of the plugin.
   */
  public function get_plugin_name() {
    return $this->plugin_name;
  }

  /**
   * The reference to the class that orchestrates the hooks with the plugin.
   *
   * @since     1.0.0
   * @return    MSync_Loader    Orchestrates the hooks of the plugin.
   */
  public function get_loader() {
    return $this->loader;
  }

  /**
   * Retrieve the version number of the plugin.
   *
   * @since     1.0.0
   * @return    string    The version number of the plugin.
   */
  public function get_version() {
    return $this->version;
  }

  public static function get_field_instance($id) {
    global $wpdb;
    $field = $wpdb->get_row('SELECT * FROM `' . $wpdb->prefix . self::$field_list_table . '` WHERE id = ' . $id);
    return $field;
  }

  public static function get_field($field_id) {
    global $wpdb;
    $field = $wpdb->get_row('SELECT * FROM `' . $wpdb->prefix . self::$form_fields_table . '` WHERE field_id = ' . $field_id);
    return $field;
  }

  public static function get_form($form_id) {
    if(is_array($form_id) || empty($form_id)) return;
    global $wpdb;
    $form_table = $wpdb->prefix . self::$forms_table;
    $fields_table = $wpdb->prefix . self::$form_fields_table;
    $form = $wpdb->get_row('SELECT * FROM `' . $form_table . '` WHERE id = ' . $form_id);
    $fields = $wpdb->get_results('SELECT * FROM `' . $fields_table . '` WHERE form_id = ' . $form_id . ' AND active = 1 ORDER BY sort_position');
    if(!empty($form) && !empty($fields)) {
      return array(
        'form' => $form,
        'fields' => $fields,
      );
    } else {
      return false;
    }
  }

  public static function delete_form($form_id) {
    global $wpdb;
    $form_table = $wpdb->prefix . 'msync_forms';
    $fields_table = $wpdb->prefix . 'msync_form_fields';
    $form_delete = $wpdb->delete($form_table, array('id' => $form_id));
    $fields_delete = $wpdb->delete($fields_table, array('form_id' => $form_id));
    return ($form_delete && $fields_delete);
  }

  public static function get_forms() {
    global $wpdb;
    $form_table = $wpdb->prefix . self::$forms_table;
    $forms = $wpdb->get_results('SELECT * FROM `' . $form_table . '`');
    return $forms;
  }
}
