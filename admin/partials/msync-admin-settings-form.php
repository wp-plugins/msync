<?php

/**
 * Displays the plugin settings form
 *
 * @link       http://magiclogix.com
 * @since      1.0.0
 *
 * @package    mSync
 * @subpackage mSync/admin/partials
 */
?>

<div class="wrap">
  <h2>Setup Marketo</h2>
  <p>You can get these settings from your Marketo Instance. Visit <strong>Admin</strong> &gt; <strong>Web Services</strong> on your Marketo Instance.</p>
  <?php settings_errors('msync_notices'); ?>
  <form method="post" action="">
    <input type="hidden" name="msync_settings_save" value="true" />
    <table class="form-table">
      <tbody>
        <tr valign="top">
          <th scope="row"><label for="msync_munchkin_id">Munchkin Account ID</label></th>
          <td>
            <input name="msync[msync_munchkin_id]" type="text" id="msync_munchkin_account_id" value="<?php echo $msync_munchkin_id; ?>" class="regular-text" placeholder="Munchkin Account ID" />
            <p class="description">Copy <strong>Munchkin Account ID</strong> from the <strong>Munchkin</strong> tab.</p>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="msync_client_id">Client ID</label></th>
          <td>
            <input name="msync[msync_client_id]" type="text" id="msync_client_id" value="<?php echo $msync_client_id; ?>" class="regular-text" placeholder="Client Secret" />
            <p class="description">Copy <strong>Client ID</strong> from <strong>Web Services</strong> tab.</p>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="msync_client_secret">Client Secret</label></th>
          <td>
            <input name="msync[msync_client_secret]" type="text" id="msync_client_secret" value="<?php echo $msync_client_secret; ?>" class="regular-text" placeholder="Client Secret" />
            <p class="description">Copy <strong>Client Secret</strong> from <strong>Web Services</strong> tab.</p>
          </td>
        </tr>
        <?php if($plugin_ready):?>
          <tr valign="top">
            <th scope="row"><label for="msync_tracking_code">Tracking Code Type</label></th>
            <td>
              <?php
                $tracking_type = array (
                  'simple' => 'Simple',
                  'async' => 'Asynchronous',
                  'jquery' => 'Asynchronous jQuery',
                );
              ?>
              <select name="msync[msync_tracking_code]" id="msync_tracking_code">
                <option value="">None</option>
                <?php foreach ($tracking_type as $key => $value):?>
                  <option value="<?php echo $key;?>" <?php if($key == $msync_tracking_code):?> selected="selected" <?php endif;?>><?php echo $value;?></option>
                <?php endforeach;?>
              </select>
              <p class="description">Choose the type of tracking code you want to include.</p>
            </td>
          </tr>
        <?php endif;?>
      </tbody>
    </table>
    <p class="submit">
      <?php submit_button( "Save Settings", 'primary', 'submit', false, array('tabindex' => 1)); ?>
      <?php if ($plugin_ready) {
          submit_button( "Test Connection", 'secondary', 'test-connection', false, array('tabindex' => 2));
          submit_button( "Sync Fields", 'secondary', 'sync-fields', false, array('tabindex' => 3));
      } ?>
    </p>
  </form>
</div>

