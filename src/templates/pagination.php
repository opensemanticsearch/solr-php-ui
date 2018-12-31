<?php

//
// Pagination
//

?>

<div class="row">
  <hr/>
</div>


<div class="pages" class="row">
  <ul class="pagination text-center" role="navigation" aria-label="Pagination">
    <li
      class="pagination-previous <?php if (!$is_prev_page): print 'disabled'; endif; ?>">
      <?php if ($is_prev_page) { ?>
      <a onclick="waiting_on();" href="<?php print $link_prev; ?>">
        <?php } ?>

        <?php print t('prev') ?>

        <?php if ($is_prev_page) { ?></a><?php } ?>

    </li>

    <li>

      <?php


      if (empty($query) and $start == 1) {
        ?>
        <?php echo t('newest_documents') ?>

        <?= $stat_limit ?>

        <?php echo t('newest_documents_of') ?>

        <?= $total ?>

        <?php echo t('newest_documents_of_total') ?>
        <?php

      }
      else {

        ?>

        <?php if ($view == "preview") { ?>

          <?php echo t('result'); ?>
          <?= $page ?>
          <?php echo t('result of'); ?>
          <?= $total ?>

        <?php } else { ?>
                
          <?php echo t('page'); ?>
          <?= $page ?>
          <?php echo t('page of'); ?>
          <?= $pages ?>
          (<?php echo t('results'); ?>

          <?= $start ?>
          <?php echo t('result to'); ?>
          <?= $end ?>
          <?php echo t('result of'); ?>
          <?= $total ?>
          )
          <?php
        }

      }


      ?>
    </li>

    <li
      class="pagination-next <?php if (!$is_next_page): print 'disabled'; endif; ?>">
      <?php if ($is_next_page) { ?>
      <a onclick="waiting_on();" href="<?php print $link_next; ?>">
        <?php } ?>

        <?php print t('next') ?>

        <?php if ($is_next_page) { ?></a><?php } ?>

    </li>
  </ul>
</div>
<div class="row">
  <hr/>
</div>
