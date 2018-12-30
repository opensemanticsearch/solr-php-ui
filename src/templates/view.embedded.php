<html>
<head>
  <title><?= t('Search') . ($query ? ': ' . htmlspecialchars($query) : '') ?></title>
  <link rel="stylesheet" href="css/foundation.css">

  <script src="js/vendor/jquery.js"></script>
  <script src="js/vendor/what-input.js"></script>
  <script src="js/vendor/foundation.js"></script>
  <script src="js/app.js"></script>

  <script type="text/javascript" src="jquery/jquery.autocomplete.js"></script>
  <script type="text/javascript" src="autocomplete.js"></script>
  <link rel="stylesheet" href="css/app.css" type="text/css"/>
  <link rel="alternate" type="application/rss+xml" title="RSS" href="<?= $link_rss ?>">
</head>
<body>
<?php

  if ($error) {
    print '<p>Error:</p><p>' . $error . '</p>';
  }

  // print the results with selected view template
  if ($view == 'list') {
    include 'templates/select_view.php';
    include 'templates/view.list.php';
    include 'templates/pagination.php';

  }
  elseif ($view == 'preview') {

    include 'templates/select_view.php';
    include 'templates/pagination.php';
    include 'templates/view.preview.php';
    include 'templates/pagination.php';

  }
  elseif ($view == 'images') {

    include 'templates/select_view.php';
    include 'templates/view.images.php';
    include 'templates/pagination.php';

  }
  elseif ($view == 'videos') {
    include 'templates/select_view.php';
    include 'templates/view.videos.php';
    include 'templates/pagination.php';
  }
  elseif ($view == 'table') {
    include 'templates/select_view.php';
    include 'templates/view.table.php';
    include 'templates/pagination.php';
  }
  elseif ($view == 'words') {

    include 'templates/select_view.php';
    include 'templates/view.words.php';

  }
  elseif ($view == 'graph') {

    include 'templates/select_view.php';
    include 'templates/view.graph.php';

  }

  elseif ($view == 'trend') {

    include 'templates/select_view.php';
    include 'templates/view.trend.php';

  }
  elseif ($view == 'map') {
  	
	 include 'templates/select_view.php';
    include 'templates/view.map.php';

  }
  elseif ($view == 'entities') {

    include 'templates/select_view.php';
    include 'templates/view.entities.php';

  }
  else {
    include 'templates/select_view.php';
    include 'templates/pagination.php';
    include 'templates/view.list.php';
    include 'templates/pagination.php';

  }

?>
</body>
</html>