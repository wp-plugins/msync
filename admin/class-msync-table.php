<?php

if(!class_exists('WP_List_Table')) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class JMerge_Table extends WP_List_Table {

  function __construct() {
    parent::__construct(array(
      'singular'=> 'Marketo Form',
      'plural' => 'Marketo Forms',
      'ajax'   => false,
    ));
  }

  function column_default($item, $column_name){
    switch($column_name){
      case 'form_name':
      case 'form_description':
        return $item->$column_name;
      default:
        return $item->$column_name;
    }
  }

  function column_form_name($item) {
    $actions = array(
      'edit' => sprintf('<a href="?page=%s&action=%s&form_id=%s">Edit</a>', 'builder','edit',$item->id),
      'delete' => sprintf('<a href="?page=%s&action=%s&form_id=%s">Delete</a>',$_REQUEST['page'],'delete',$item->id),
    );

    //Return the title contents
    return sprintf('%1$s%2$s',
      /*$1%s*/ $item->form_name,
      /*$2%s*/ $this->row_actions($actions)
    );
  }

  function column_cb($item) {
    return sprintf(
      '<input type="checkbox" name="%1$s[]" value="%2$s" />',
      /*$1%s*/ $this->_args['singular'],
      /*$2%s*/ $item->id
    );
  }

  function extra_tablenav($which) {
    if ( $which == "top" ){
      //The code that goes before the table is here
      // echo"Hello, I'm before the table";
    }
    if ( $which == "bottom" ){
      //The code that goes after the table is there
      // echo"Hi, I'm after the table";
    }
  }

  function get_columns() {
    return $columns= array(
      'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
      'form_name'=>__('Name'),
      'form_description'=>__('Description'),
    );
  }

  function get_bulk_actions() {
    $actions = array(
      'delete' => 'Delete'
    );
    return $actions;
  }

  function process_bulk_action() {
    if( 'delete' === $this->current_action() ) {
      $form_id = $_REQUEST['form_id'];
      $form_delete = MSync::delete_form($form_id);
      if($form_delete)
        MSync_Admin::msync_notice('Form deleted');
      else
        MSync_Admin::msync_notice('Could not delete forms: '. $form_delete, 'error');
    }
  }

  public function get_sortable_columns() {
    return $sortable = array(
      'form_name'=> array('form_name', true),
      'form_description'=> array('form_description', true),
    );
  }

  function prepare_items() {
    global $wpdb;
    $per_page = 5;
    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();
    $this->_column_headers = array($columns, $hidden, $sortable);
    $this->process_bulk_action();

    /* -- Preparing your query -- */
    $query = "SELECT * FROM `" . $wpdb->prefix . MSync::$forms_table . "`";

    /* -- Ordering parameters -- */
    //Parameters that are going to be used to order the result
    $orderby = !empty($_GET["orderby"]) ? ($_GET["orderby"]) : 'ASC';
    $order = !empty($_GET["order"]) ? ($_GET["order"]) : '';
    if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }

    /* -- Pagination parameters -- */
    //Number of elements in your table?
    $totalitems = $wpdb->query($query); //return the total number of affected rows
    //How many to display per page?
    $perpage = 5;
    //Which page is this?
    $paged = !empty($_GET["paged"]) ? ($_GET["paged"]) : '';
    //Page Number
    if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
    //How many pages do we have in total?
    $totalpages = ceil($totalitems/$perpage);
    //adjust the query to take pagination into account
    if(!empty($paged) && !empty($perpage)){
      $offset=($paged-1)*$perpage;
      $query.=' LIMIT '.(int)$offset.','.(int)$perpage;
    }

    /* -- Register the pagination -- */
    $this->set_pagination_args(array(
      "total_items" => $totalitems,
      "total_pages" => $totalpages,
      "per_page" => $perpage,
    ));

    /* -- Fetch the items -- */
    $this->items = $wpdb->get_results($query);
  }
}
