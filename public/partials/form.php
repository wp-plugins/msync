<?php
  $form = $form_data['form'];
  $fields = $form_data['fields'];
  // echo "<pre>"; print_r($form_data); echo "</pre>";
?>
<?php if(isset($_REQUEST['msync-form'])):
  $form_validated = TRUE;
  $validation_errors = array();
  foreach ($_REQUEST['marketo-field'] as $key => $value) {
    $field_validate = MSync_Public::field_validate($key, $value);
    if (!$field_validate['validate']) {
      $validation_errors[$key] = $field_validate['error'];
      $form_validated = FALSE;
    } else {
      $validation_errors[$key] = FALSE;
    }
  }
  if ($form_validated) {
    MSync_Public::push_lead($_REQUEST['marketo-field']);
    switch($form->followup_type) {
      case 'post':
        $followup_url = get_post_permalink($form->followup_destination);
        break;
      case 'url':
        $followup_url = $form->followup_destination;
        break;
    }
    // wp_redirect($followup_url, 301);
    // exit;
    echo "<p>You will be redirected to $followup_url</p>";
  }
  ?>
  <?php //echo "<pre>"; print_r($_REQUEST['marketo-field']); echo "</pre>";?>
  <?php //echo "<pre>"; print_r($validation_errors); echo "</pre>";?>
<?php endif;?>
<?php if(isset($extra_class)) {$post_class = "[" . $extra_class."]";} else {$post_class = '';}?>
<div class="msync-form-wrapper">
  <form name="marketo-form-<?php echo $form->id . $post_class; ?>" method="POST" action="" id="msync-form-<?php echo $form->id . $post_class;?>" class="msync-form">
    <p class="msync-instruction">
      <?php echo $form->form_description;?>
    </p>
    <div class="msync-form-fields">
      <input type="hidden" name="msync-form<?php echo $post_class;?>">
      <?php foreach($fields as $field):?>
        <?php $additional_class = ''; ?>
        <div class="msync-form-field msync-form-field-<?php print $field->field_id;?>">
          <label for="marketo-field-<?php echo $field->field_id;?>">
            <?php echo $field->label;?>
            <?php if($field->is_required):?> <span>*</span><?php $additional_class .= ' compulsory'; endif;?>
          </label>
          <div class="small validation">
            <?php
              if(isset($validation_errors[$field->field_id]) && $validation_errors[$field->field_id] !== FALSE):
                $additional_class .= " error"; ?>
              <p><?php echo $validation_errors[$field->field_id];?></p>
            <?php endif;?>
          </div>
          <?php switch ($field->input_type){
            case "text": default:?>
              <input type="text" name="marketo-field[<?php print $field->field_id;?>]" placeholder="<?php print $field->label;?>" class="<?php echo $additional_class;?>"/>
              <?php break;
            case "textarea":?>
              <textarea name="marketo-field[<?php print $field->field_id;?>]" placeholder="<?php print $field->label;?>" class="<?php echo $additional_class;?>"></textarea>
              <?php break;
            case "select":?>
              <select name="marketo-field[<?php print $field->field_id;?>]" class="<?php echo $additional_class;?>">
              </select>
              <?php break;
            case "hidden":?>
              <input type="hidden" name="marketo-field[<?php print $field->field_id;?>]" value="<?php print $field->default;?>"/>
              <?php break;
          }?>
        </div>
      <?php endforeach;?>
    </div>
    <div class="msync-actions">
      <br>
      <?php $button_label = ($form->button_label != '') ? $form->button_label : 'Submit Form';?>
      <input type="submit" name="submit" value="<?php echo $button_label;?>" class="msync-submit">
    </div>
  </form>
</div>
