<?php

// Show sort selectors

if ($view != "words" && $view != 'trend') {

  ?>
  <div id="sort" class="row float-right">

    <script type="text/javascript">
      function handleSelectSort() {
        waiting_on();
        window.location = document.getElementById('select_sort').value;
      }
    </script>
    <label>Sort
      <select id="select_sort" onchange="handleSelectSort()">

        <?php
        if (is_null($sort)) {
          print '<option selected value="' . buildurl($params, "sort", NULL, 's', 1) . '">' . t('Relevance') . '</option>';
        }
        else {
          print '<option value="' . buildurl($params, "sort", NULL, 's', 1) . '">' . t('Relevance') . '</option>';
        }
        if ($sort == 'newest') {
          print '<option selected value="' . buildurl($params, "sort", 'newest', 's', 1) . '">' . t('Newest') . '</option>';
        }
        else {
          print '<option value="' . buildurl($params, "sort", 'newest', 's', 1) . '">' . t('Newest') . '</option>';
        }
        if ($sort == 'oldest') {
          print '<option selected value="' . buildurl($params, "sort", 'oldest', 's', 1) . '">' . t('Oldest') . '</option>';
        }
        else {
          print '<option value="' . buildurl($params, "sort", 'oldest', 's', 1) . '">' . t('Oldest') . '</option>';
        }

        if ($sort != NULL && $sort != 'newest' && $sort != 'oldest') {

          print '<option selected value="' . buildurl($params, "sort", $sort, 's', 1) . '">' . htmlspecialchars($sort) . '</option>';

        }
        ?>


      </select>
    </label>


  </div>


  <?php

}

?>
