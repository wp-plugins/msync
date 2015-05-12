<div id="msync_popup_container" style="display:none;">
    <h3>Select the appropriate form you wish to use. </h3>
    <br>
    <select name="marketo_form_id">
      <?php if(is_array($marketo_forms)):?>
        <?php foreach($marketo_forms as $form):?>
          <option value="<?php echo $form->id;?>"><?php echo $form->form_name;?></option>
        <?php endforeach;?>
      <?php endif;?>
    </select>
    <p class="description">Simply select the form template you think will fit best to use it within this post. If you need to edit a form or create a new form type, go to the MSync tab and either edit an existing one or create a new one.</p>
    <p><input type="button" class="button-primary" id="msync-add-shortcode-button" value="Insert Form"></p>
</div>
