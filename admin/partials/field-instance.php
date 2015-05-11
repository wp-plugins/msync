<?php switch ($field_instance->data_type) {
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
//echo "<pre>"; print_r($field_instance); echo "</pre>";
?>
<div class="mkto_field mkto_field-<?php echo $field_instance->id;?> required" data-field-instance-id="<?php echo $field_instance->id;?>">
  <input type="hidden" value="1" name="mkto_field[<?php echo $field_instance->id;?>][required]" id="mkto_field-<?php print $field_instance->id;?>-required"/>
  <input type="hidden" value="" name="mkto_field[<?php echo $field_instance->id;?>][default]" id="mkto_field-<?php print $field_instance->id;?>-default"/>
  <input type="hidden" value="" name="mkto_field[<?php echo $field_instance->id;?>][placeholder]" id="mkto_field-<?php print $field_instance->id;?>-placeholder"/>
  <input type="hidden" value="<?php echo $field_type;?>" name="mkto_field[<?php echo $field_instance->id;?>][type]" id="mkto_field-<?php print $field_instance->id;?>-type"/>
  <input type="hidden" value="" name="mkto_field[<?php echo $field_instance->id;?>][validation]" id="mkto_field-<?php print $field_instance->id;?>-validation"/>
  <input type="hidden" value="<?php echo $field_instance->display_name;?>" name="mkto_field[<?php echo $field_instance->id;?>][label]" id="mkto_field-<?php print $field_instance->id;?>-label"/>
  <input type="hidden" value="<?php echo $field_instance->instruction;?>" name="mkto_field[<?php echo $field_instance->id;?>][instruction]" id="mkto_field-<?php print $field_instance->id;?>-instruction"/>
  <input type="hidden" value="<?php echo $field_instance->label_position;?>" name="mkto_field[<?php echo $field_instance->id;?>][label_position]" id="mkto_field-<?php print $field_instance->id;?>-label-position"/>
  <label><?php echo $field_instance->display_name;?></label>
  <span class="dummy-input dummy-input-<?php echo $field_type;?>"></span>
  <span class="delete_control dashicons dashicons-no" data-field-instance-id="<?php echo $field_instance->id;?>" title="remove field"></span>
</div>
