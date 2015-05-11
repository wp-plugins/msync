<?php //echo "<pre>"; print_r($field); echo "</pre>";?>
<div class="mkto_field_setting">
  <label>Label:</label>
  <input type="text" value="<?php echo $field->label;?>" name="field[label]" id="mkto-field-label">
</div>
<div class="mkto_field_setting">
  <label>Is Required:</label>
  <input type="checkbox" name="field[required]" id="mkto-field-required" <?php if($field->is_required == 1):?>checked='checked'<?php endif;?>>
</div>
<div class="mkto_field_setting">
  <label>Input Type:</label>
  <?php
    $field_types = array (
      'text' => 'Text',
      'textarea' => 'Textarea',
      'select' => 'Select',
      'checkbox' => 'Checkbox',
      'radio' => 'Radio Button',
      'hidden' => 'Hidden Field',
    );
  ?>
  <select name="field[type]" id="mkto-field-type">
    <?php foreach ($field_types as $key => $value):?>
      <option value="<?php echo $key;?>" <?php if($key == $field->input_type):?> selected="selected" <?php endif;?>><?php echo $value;?></option>
    <?php endforeach;?>
  </select>
</div>
<div class="mkto_field_setting" title="How should the field be validated?">
  <label>Validation:</label>
  <?php
    $validation = array (
      'number' => 'Number Only',
      'email' => 'Email Address',
      'uri' => 'Web Address',
      'phone' => 'US Phone Number',
      'int_phone' => 'International Phone Number',
    );
  ?>
  <select name="field[validation]" id="mkto-field-validation">
    <option value="" title="Validates input exists if field is required.">Default</option>
    <?php foreach ($validation as $key => $value):?>
      <option value="<?php echo $key;?>" <?php if($key == $field->validation):?> selected="selected" <?php endif;?>><?php echo $value;?></option>
    <?php endforeach;?>
  </select>
  <div class="description">
    <em>How should the field be validated?</em>
  </div>
</div>
<div class="mkto_field_setting">
  <label>Default:</label>
  <input type="text" id="mkto-field-default" value="<?php echo $field->default;?>">
  <div class="description">
    <em>Not displayed to the user, but sent to Marketo</em>
  </div>
</div>
<div class="mkto_field_setting">
  <label>Placeholder:</label>
  <input type="text" id="mkto-field-placeholder" value="<?php echo $field->placeholder;?>">
  <div class="description">
    <em>Only works on newer browsers.</em>
  </div>
</div>
<div class="mkto_field_setting">
  <label>Label Position:</label>
  <?php
    $label_positions = array (
      'above' => 'Above Element',
      'left' => 'Left of Element',
      'below' => 'Below Element',
      'right' => 'Right of Element',
      'inside' => 'Inside Element',
      'no' => 'No Label',
    );
  ?>
  <select name="field[label_position]" id="mkto-field-label-position">
    <?php foreach ($label_positions as $key => $value):?>
      <option value="<?php echo $key;?>" <?php if($key == $field->label_position):?> selected="selected" <?php endif;?>><?php echo $value;?></option>
    <?php endforeach;?>
  </select>
</div>
<div class="mkto_field_setting">
  <label>Instruction:</label>
  <textarea id="mkto-field-instruction"><?php echo $field->instruction;?></textarea>
</div>
<div class="mkto-publishing-actions form-publishing-actions">
  <button type="button" class="button">Apply</button>
  <div class="clear"></div>
</div>
