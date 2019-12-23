<?php
// Determine which tab is active for rendering.
$view_selectors = array(
  'list' => t('List'),
  'preview' => t('Preview'),
  'entities' => t('Entities'),
  'images' => t('Images'),
  'videos' => t('Videos'),
  'audios' => t('Audios'),
  'map' => t('Map'),
);

$analyse_dropdowns = array(
  'morphology' => t('Fuzzy search for name variants'),
  'entities' => t('Named entities'),
  'graph' => t('view_graph'),
  'trend' => t('view_trend'),
  'table' => t('Table'),
  'words' => t('view_words'),
);

$tabs = [];
foreach ($view_selectors as $selector => $title) {
  $tab = [
    'class' => ['button', 'secondary'],
    'title' => $title,
    'onclick' => 'waiting_on();',
  ];

  switch ($selector) {
    case 'list':
      if ($view === 'preview') {
        // if switching back from preview mode to list, dont reset start to first result
        $pagestart = (floor(($start-1) / $limit_list) * $limit_list ) + 1;
        $tab['url'] = buildurl($params, 'view', NULL, 's', $pagestart);
      }
      else {
        // Switching from other view like images or table, so reset start to first result
        $tab['url'] = buildurl($params, 'view', NULL, 's', 1);
      }
      break;

    default:
      $tab['url'] = buildurl($params, 'view', $selector, 's', 1);
      break;
  }

  if ($view === $selector) {
    $tab['class'][] = 'active';
    $tab['url'] = '#';
  }

  $tabs[$selector] = $tab;
}

$analyse_items = [];
foreach ($analyse_dropdowns as $anl_item => $title) {
  $item = [
    'class' => ['button'],
    'title' => $title,
    'onclick' => 'waiting_on();',
  ];

  switch ($anl_item) {

    case 'morphology':
      $item['url'] = $cfg['morphology'] . rawurlencode($query);
      break;

    case 'table':
      $item['url'] = buildurl($params, 'view', $anl_item, 's', null);
      break;

    case 'trend':
      $item['url'] = buildurl($params, 'view', $anl_item, 's', null);
      break;

    case 'words':
      $item['url'] = buildurl($params, 'view', $anl_item, 's', null);
      break;

    case 'entities':
      $item['url'] = buildurl($params, 'view', 'entities', 's', null);
      break;

    case 'graph':
      $item['url'] = buildurl($params, 'view', 'graph', 's', null);
      break;
  }
  $analyse_items[$anl_item] = $item;
}
?>

<div id="select_view" class="row">

  <div class="button-group">

    <!-- Per View (List, Image, ...) Selector -->
    <?php foreach ($tabs as $selector => $detail): ?>
      <a class="<?= implode(' ', $detail['class']) ?>"
         href="<?= $detail['url'] ?>"><?= $detail['title'] ?></a>
    <?php endforeach; ?>

    <button class="button secondary dropdown" type="button"
            data-toggle="analyze-dropdown"><?= t('view_analytics'); ?></button>

    <!-- Analyse dropdown -->
    <div class="dropdown-pane" id="analyze-dropdown" data-dropdown
         data-auto-focus="true">

      <?php foreach ($analyse_items as $item => $detail): ?>
        <a class="<?= implode(' ', $detail['class']) ?>"
           href="<?= $detail['url'] ?>"><?= $detail['title'] ?></a>
      <?php endforeach; ?>

    </div>

    <?php
       if ($view == 'list' || $view == 'images' || $view == 'videos' || $view == 'audios') {
         include 'templates/select_sort.php';
       }
    ?>
  </div>

</div>
