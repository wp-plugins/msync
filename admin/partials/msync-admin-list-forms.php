<div class="wrap">
  <h2>Marketo Forms <a class="add-new-h2" href="<?php menu_page_url('builder'); ?>">Add New</a></h2>
  <?php
    $jmerge_table = new JMerge_Table();
    $jmerge_table->prepare_items();
    $jmerge_table->display();
  ?>

</div>
