<?php if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Adds MSync widget.
 */

class MSync_Widget extends WP_Widget {
  /**
   * Register widget with WordPress.
   */
  public function __construct() {
    parent::__construct(
      'msync_widget', // Base ID
      'MSync Widget', // Name
      array( 'description' => __( 'MSync Widget', 'msync' ), ) // Args
    );
  }

  /**
   * Front-end display of widget.
   *
   * @see WP_Widget::widget()
   *
   * @param array $args     Widget arguments.
   * @param array $instance Saved values from database.
   */
  public function widget( $args, $instance ) {
    extract( $args );
    $form_id = $instance['form_id'];
    $form_data = MSync::get_form( $form_id );
    if(is_array($form_data)) {
      $title = $form_data['form']->form_name;
      // echo "<pre>"; print_r($form_row); echo "</pre>";

      $title = apply_filters( 'widget_title', $title );
      $display_title = $instance['display_title'];

      echo $before_widget;
      if ( ! empty( $title ) AND $display_title == 1 )
        echo $before_title . $title . $after_title;
      MSync_Public::render_form($form_data);
      echo $after_widget;
    }
  }

  /**
   * Sanitize widget form values as they are saved.
   *
   * @see WP_Widget::update()
   *
   * @param array $new_instance Values just sent to be saved.
   * @param array $old_instance Previously saved values from database.
   *
   * @return array Updated safe values to be saved.
   */
  public function update( $new_instance, $old_instance ) {

    $instance = array();
    $instance['form_id'] = $new_instance['form_id'];
    $instance['display_title'] = $new_instance['display_title'];

    return $instance;
  }

  /**
   * Back-end widget form.
   *
   * @see WP_Widget::form()
   *
   * @param array $instance Previously saved values from database.
   */
  public function form( $instance ) {
    if( isset( $instance['form_id'] ) ){
      $form_id = $instance['form_id'];
    }else{
      $form_id = '';
    }

    if( isset( $instance['display_title'] ) ){
      $display_title = $instance['display_title'];
    }else{
      $display_title = 0;
    }

    ?>
    <p>
      <label>
        <?php _e('Display Title', 'msync' ); ?>
        <input type="hidden" value="0" name="<?php echo $this->get_field_name( 'display_title' ); ?>">
        <input type="checkbox" value="1" id="<?php echo $this->get_field_id( 'display_title' ); ?>" name="<?php echo $this->get_field_name( 'display_title' ); ?>" <?php checked( $display_title, 1 );?>>
      </label>
    </p>
    <p>
    <select id="<?php echo $this->get_field_id( 'form_id' ); ?>" name="<?php echo $this->get_field_name( 'form_id' ); ?>">
      <option value="0">-- <?php _e('None', 'msync');?></option>
      <?php
      $all_forms = MSync::get_forms();
      foreach($all_forms as $form){
        $title = $form->form_name;
        $id = $form->id;
        ?>
        <option value = "<?php echo $id;?>" <?php selected( $id, $form_id );?>>
        <?php echo $title;?>
        </option>
        <?php
      }
      ?>
      </select>
    </p>

    <?php
  }

  public function register_widget(){
    register_widget('msync_widget');
  }
}
