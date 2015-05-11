<div id="msync_popup_container" style="display:none;">
    <h3>Choose your Form</h3>
    <br>
    <select name="marketo_form_id">
      <?php if(is_array($marketo_forms)):?>
        <?php foreach($marketo_forms as $form):?>
          <option value="<?php echo $form->id;?>"><?php echo $form->form_name;?></option>
        <?php endforeach;?>
      <?php endif;?>
    </select>
    <p class="description">Description.</p>
    <p><input type="button" class="button-primary" id="msync-add-shortcode-button" value="Insert Form"></p>
</div>
