<?php
// Determine which tab is active for rendering.
$view_selectors = array(
  'list' => t('List'),
  'preview' => t('Preview'),
  'images' => t('Images'),
  'videos' => t('Videos'),
  'audios' => t('Audios'),
  'table' => t('Table'),
);
$analyse_dropdowns = array(
  'trend' => t('view_trend'),
  'words' => t('view_words'),
  'graph_ne' => t('Named entities'),
  'graph_co' => t('Connections'),
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

    case 'table':
      $tab['url'] = buildurl($params, 'view', $selector, 's', FALSE);
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

$avs = [];
foreach ($analyse_dropdowns as $a_d => $title) {
  $av = [
    'class' => ['button'],
    'title' => $title,
    'onclick' => 'waiting_on();',
  ];

  switch ($a_d) {
    case 'trend':
      $av['url'] = buildurl($params, 'view', $a_d, 's', FALSE);
      break;
      
    case 'words':
      $av['url'] = buildurl($params, 'view', $a_d, 's', FALSE);
      break;
      
    case 'graph_ne':
      $av['url'] = buildurl($params, 'view', 'graph', 's', 1);
      break;

    case 'graph_co':
      $av['url'] = buildurl($params, 'view', 'graph', 's', FALSE);
      break;
    else {
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

      <?php foreach ($avs as $av => $detail): ?>
        <a class="<?= implode(' ', $detail['class']) ?>"
           href="<?= $detail['url'] ?>"><?= $detail['title'] ?></a>
      <?php endforeach; ?>

    </div>

  </div>

</div>
