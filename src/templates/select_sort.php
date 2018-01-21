<?php if ($view != "words" && $view != 'trend'): ?>
  <div id="sort" class="row float-right">
    <label for="select_sort"><?= t('Sort') ?></label>
    <select id="select_sort">

      <?php $sort_relevance = (is_null($sort)) ? 'selected' : ''; ?>
      <option <?= $sort_relevance ?>
        value="<?= buildurl($params, "sort", NULL, 's', 1) ?>"><?= t('Relevance') ?></option>
      '

      <?php $sort_newest = (isset($sort) && $sort == 'newest') ? 'selected' : ''; ?>
      <option <?= $sort_newest ?>
        value="<?= buildurl($params, "sort", 'newest', 's', 1) ?>"><?= t('Newest') ?></option>

      <?php $sort_oldest = (isset($sort) && $sort == 'oldest') ? 'selected' : ''; ?>
      <option <?= $sort_oldest ?>
        value="<?= buildurl($params, "sort", 'oldest', 's', 1) ?>"><?= t('Oldest') ?></option>

      <?php $sort_other = ($sort != NULL && $sort != 'newest' && $sort != 'oldest') ? 'selected' : ''; ?>
      <option <?= $sort_other ?>
        value="<?= buildurl($params, "sort", $sort, 's', 1) ?>"><?= htmlspecialchars($sort) ?></option>

    </select>
    <script type="text/javascript">
      //$.ready(function () {
      $('#select_sort').change(function () {
        var dest = $(this).val();
        waiting_on();
        window.location = dest;
      });
      //});
    </script>
  </div>
<?php endif; ?>
