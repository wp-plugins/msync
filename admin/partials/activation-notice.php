<style type="text/css">
  .msync_configure {
    background: -moz-linear-gradient(80% 100% 120deg, #4f800d, #83af24) repeat scroll 0 0 #83af24;
    border: 1px solid #4f800d;
    border-radius: 3px;
    margin: 15px 0;
    min-width: 825px;
    overflow: hidden;
    padding: 5px;
    position: relative;
  }
  .msync_configure .msync_button_container {
    background: none repeat scroll 0 0 #def1b8;
    border-radius: 2px;
    cursor: pointer;
    display: inline-block;
    padding: 5px;
    width: 266px;
  }
  .msync_configure .msync_description {
    color: #e5f2b1;
    font-size: 15px;
    left: 285px;
    margin-left: 25px;
    position: absolute;
    top: 22px;
    z-index: 1000;
  }
  .msync_configure .msync_description strong {
    color: #FFF;
    font-weight: normal;
  }
  .msync_configure .msync_button_border {
    background: -moz-linear-gradient(0% 100% 90deg, #0079b1, #029dd6) repeat scroll 0 0 #029dd6;
    border: 1px solid #006699;
    border-radius: 2px;
  }
  .msync_configure .msync_button {
    -moz-border-bottom-colors: none;
    -moz-border-left-colors: none;
    -moz-border-right-colors: none;
    -moz-border-top-colors: none;
    background: -moz-linear-gradient(0% 100% 90deg, #0079b1, #029dd6) repeat scroll 0 0 #029dd6;
    border-color: #06b9fd #029dd6 #029dd6;
    border-image: none;
    border-radius: 2px;
    border-right: 1px solid #029dd6;
    border-style: solid;
    border-width: 1px;
    color: #fff;
    font-size: 15px;
    font-weight: bold;
    padding: 9px 0 8px;
    text-align: center;
  }
</style>
<div class="updated" style="padding: 0; margin: 0; border: none; background: none;">
  <form method="POST" action="<?php echo admin_url();?>options-general.php?page=msync_settings" name="msync_configure">
    <div class="msync_configure">
      <div onclick="document.msync_configure.submit();" class="msync_button_container">
        <div class="msync_button_border">
          <div class="msync_button">Configure MSync</div>
        </div>
      </div>
      <div class="msync_description"><strong>Almost done</strong> - Configure your Marketo Credentials now.</div>
    </div>
  </form>
</div>
