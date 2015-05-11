<div class="wrap">
  <h1>Form Builder</h1>
  <form action="" id="post" method="post">
    <div id="poststuff">
      <div id="msync-form-builder" class="columns-2 metabox-holder">
        <div id="post-body-content">
          <div id="titlediv">
            <div id="titlewrap">
              <!-- <label for="title" id="title-prompt-text" class="">Form Name Here</label> -->
              <input type="text" autocomplete="off" spellcheck="true" id="title" value="<?php if(isset($form_data['form']->form_name)) echo $form_data['form']->form_name;?>" size="30" name="form[title]" placeholder="Form Name Here">
              <?php if (isset($form_data['form']->id)):?>
              <input type="hidden" name="form[id]" value="<?php echo $form_data['form']->id;?>">
              <?php endif;?>
            </div>
          </div>
          <div id="marketo-form-canvas">
            <h3 class="hndle ui-sortable-handle"><span>Form Canvas</span></h3>
            <div class="form-canvas">
              <?php
              if (isset($form_data) && is_array($form_data)):
                foreach($form_data['fields'] as $field):
                  $required = ($field->is_required == 1) ? 'required' : '';
                  ?>
                  <div class="mkto_field mkto_field-<?php echo $field->field_instance_id . " " . $required;?>" data-field-instance-id="<?php echo $field->field_instance_id;?>" data-field-id="<?php echo $field->field_id;?>">
                    <input type="hidden" value="<?php echo $field->is_required;?>" name="mkto_field[<?php echo $field->field_instance_id;?>][required]" id="mkto_field-<?php echo $field->field_instance_id;?>-required" class="field_required">
                    <input type="hidden" value="<?php echo $field->default;?>" name="mkto_field[<?php echo $field->field_instance_id;?>][default]" id="mkto_field-<?php echo $field->field_instance_id;?>-default" class="field-default">
                    <input type="hidden" value="<?php echo $field->placeholder;?>" name="mkto_field[<?php echo $field->field_instance_id;?>][placeholder]" id="mkto_field-<?php echo $field->field_instance_id;?>-placeholder" class="field-placeholder">
                    <input type="hidden" value="<?php echo $field->input_type;?>" name="mkto_field[<?php echo $field->field_instance_id;?>][type]" id="mkto_field-<?php echo $field->field_instance_id;?>-type" class="field-type">
                    <input type="hidden" value="<?php echo $field->validation;?>" name="mkto_field[<?php echo $field->field_instance_id;?>][validation]" id="mkto_field-<?php echo $field->field_instance_id;?>-validation" class="field-validation">
                    <input type="hidden" value="<?php echo $field->label;?>" name="mkto_field[<?php echo $field->field_instance_id;?>][label]" id="mkto_field-<?php echo $field->field_instance_id;?>-label">
                    <label><?php echo $field->label;?></label>
                    <span class="dummy-input dummy-input-<?php echo $field->input_type;?>"></span>
                    <input type="hidden" value="<?php echo $field->field_id;?>" name="mkto_field[<?php echo $field->field_instance_id;?>][field_id]" />
                    <input type="hidden" value="<?php echo $field->instruction;?>" name="mkto_field[<?php echo $field->field_instance_id;?>][instruction]" id="mkto_field-<?php print $field->field_instance_id;?>-instruction"/>
                    <input type="hidden" value="<?php echo $field->label_position;?>" name="mkto_field[<?php echo $field->field_instance_id;?>][label_position]" id="mkto_field-<?php print $field->field_instance_id;?>-label-position"/>
                    <span class="delete_control dashicons dashicons-no" data-field-id="<?php echo $field->field_instance_id;?>" title="remove field"></span>
                  </div>
                <?php endforeach;
              endif;?>
            </div>
            <button id="mkto-fake-submit" class="button button-primary button-large" disabled="disabled">Submit Form</button>
          </div>
          <div class="postbox" id="followup" style="display: block;">
            <div title="Click to toggle" class="handlediv"><br></div><h3 class="hndle ui-sortable-handle"><span>Followup</span></h3>
            <div class="inside">
              <p>Choose what should happen when the form is submitted.</p>
              <div class="mkto_form_setting">
                <label for="form-followup-type">Follow-up Type</label for="form-followup">
                <?php
                  $followup_types = array (
                    'none' => 'No Redirect',
                    'url' => 'URL',
                    'post' => 'Post',
                  );
                  $active_type = isset($form_data['form']->followup_type) ? $form_data['form']->followup_type : 'none';
                  $active_detination = isset($form_data['form']->followup_destination) ? $form_data['form']->followup_destination : '';
                ?>
                <select name="form[followup-type]" id="form-followup-type">
                  <?php foreach ($followup_types as $key => $value):?>
                    <option value="<?php echo $key;?>" <?php if($key == $active_type):?> selected="selected" <?php endif;?>><?php echo $value;?></option>
                  <?php endforeach;?>
                </select>
              </div>
              <div class="mkto_form_setting followup-wrapper followup-url-wrapper <?php if($active_type == 'url') echo "active";?> ">
                <label for="followup-url-input">Follow up URL</label>
                <input type="text" name="form[followup-url]" id="followup-url-input" value="<?php echo $active_detination;?>"/>
              </div>
              <div class="mkto_form_setting followup-wrapper followup-post-wrapper <?php if($active_type == 'post') echo "active";?>">
                <label for="followup-post-input">Follow up POST</label>
                <select name="form[followup-post]">
                  <?php
                  global $post;
                  $args = array( 'numberposts' => -1);
                  $posts = get_posts($args);
                  foreach( $posts as $post ) : setup_postdata($post); ?>
                  <option value="<?php echo $post->ID;?>" <?php if($post->ID == $active_detination):?> selected="selected" <?php endif;?>><?php the_title(); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div id="msync-form-properties" class="postbox-container">
          <div id="side-sortable" class="meta-box-sortable ui-sortable">

            <div id="formdiv" class="postbox">
              <div title="Click to toggle" class="handlediv"><br></div>
              <h3 class="hndle ui-sortable-handle"><span>Form Properties</span></h3>
              <div class="inside">
                <div id="form-meta">
                  <div class="mkto_form_setting">
                    <label for="form-description">Description</label>
                    <textarea name="form[description]" id="form-description" cols="16" rows="5"><?php if(isset($form_data['form']->form_description)) echo $form_data['form']->form_description;?></textarea>
                  </div>
                  <div class="mkto_form_setting">
                    <label for="form-submit-button">Button Label</label>
                    <input type="text" name="form[button_label]" id="form-submit-button" cols="16" value="<?php if(isset($form_data['form']->button_label)) echo $form_data['form']->button_label; else echo "Submit Form";?>"/>
                  </div>
                </div>
                <div class="mkto-publishing-actions form-publishing-actions">
                  <span class="spinner"></span>
                  <input type="hidden" value="Publish" id="original_publish" name="original_publish">
                  <input type="submit" accesskey="p" value="Save Form" class="button button-primary button-large" id="publish" name="publish">
                  <div class="clear"></div>
                </div>
              </div>
            </div>

            <div id="fielddiv" class="postbox">
              <div title="Click to toggle" class="handlediv"><br></div>
              <h3 class="hndle ui-sortable-handle"><span>Field Specific</span></h3>
              <div class="inside">
                <div id="mkto-field-settings"></div>
              </div>
            </div>

            <div id="marketo-formfieldsdiv" class="postbox">
              <div title="Click to toggle" class="handlediv"><br></div>
              <h3 class="hndle ui-sortable-handle"><span>Template Form Fields</span></h3>
              <div class="inside">
                <ul class="marketo-form-fields">
                  <?php print $msync_lead_fields;?>
                </ul>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </form>
</div>
