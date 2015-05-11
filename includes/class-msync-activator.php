<?php

/**
 * Fired during plugin activation
 *
 * @link       http://magiclogix.com
 * @since      1.0.0
 *
 * @package    mSync
 * @subpackage mSync/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    mSync
 * @subpackage mSync/includes
 * @author     Sam Timalsina <stimalsina@magiclogix.com>
 */
class MSync_Activator {

  /**
   * Activate mSync.
   *
   * Activate mSync.
   *
   * @since    1.0.0
   */
  public static function activate() {
    self::activate_field_list();
    self::activate_forms();
    self::activate_form_fields();
  }


  public static function activate_field_list() {
    global $wpdb;
    $table_name = $wpdb->prefix . MSync::$field_list_table;
    $charset_collate = $wpdb->get_charset_collate();
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      $sql = "CREATE TABLE $table_name (
        `id` smallint(6) NOT NULL COMMENT 'id',
        `display_name` varchar(100) NOT NULL COMMENT 'display_name',
        `rest_name` varchar(50) NOT NULL COMMENT 'rest_name',
        `data_type` varchar(20) NOT NULL COMMENT 'data_type',
        `rest_readonly` tinyint(1) NOT NULL COMMENT 'rest_readonly',
        UNIQUE KEY (id)
      ) COMMENT='Field definitions from the Marketo API' $charset_collate;";

      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $sql );
    }
  }

  public static function activate_forms() {
    global $wpdb;
    $table_name = $wpdb->prefix . MSync::$forms_table;
    $charset_collate = $wpdb->get_charset_collate();
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      $sql = "CREATE TABLE $table_name (
        `id` smallint(6) NOT NULL AUTO_INCREMENT COMMENT 'form_id',
        `form_name` varchar(255) NOT NULL COMMENT 'Form Name',
        `form_description` varchar(255) NOT NULL COMMENT 'Form Description',
        `button_label` varchar(100) NOT NULL COMMENT 'Label on the Submit button',
        `followup_type` varchar(5) NOT NULL COMMENT 'type of followup',
        `followup_destination` varchar(255) NOT NULL COMMENT 'followup_destination',
        UNIQUE KEY (id)
      ) COMMENT='Marketo Forms' $charset_collate;";
      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $sql );
    }
  }

  public static function activate_form_fields() {
    global $wpdb;
    $table_name = $wpdb->prefix . MSync::$form_fields_table;
    $charset_collate = $wpdb->get_charset_collate();
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      $sql = "CREATE TABLE $table_name (
        `field_id` smallint(6) NOT NULL AUTO_INCREMENT COMMENT 'field_id',
        `field_instance_id` smallint(6) NOT NULL COMMENT 'id of msync_field_list',
        `form_id` tinyint(4) NOT NULL COMMENT 'Form Id that this field belongs to',
        `label` varchar(100) NOT NULL COMMENT 'Label of Field',
        `placeholder` varchar(255) NOT NULL COMMENT 'Placeholder',
        `instruction` varchar(255) NOT NULL COMMENT 'Instruction',
        `input_type` varchar(20) NOT NULL COMMENT 'Input type of the field',
        `is_required` tinyint(1) NOT NULL COMMENT 'Is the field compulsory',
        `validation` varchar(10) NOT NULL COMMENT 'Validation Name',
        `default` varchar(255) NOT NULL COMMENT 'Default Value',
        `sort_position` tinyint(4) NOT NULL COMMENT 'Sort position',
        `active` tinyint(1) NOT NULL COMMENT 'Is active',
        `label_position` varchar(10) NOT NULL COMMENT 'Label Position',
        UNIQUE KEY (field_id)
      ) COMMENT='Marketo Form fields' $charset_collate;";

      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $sql );
    }
  }
}
