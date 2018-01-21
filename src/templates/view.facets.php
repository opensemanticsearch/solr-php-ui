<div id="facets">
  <?php

  // todo: hover durchgestrichen bei (x)-Werten

  // if active facets, show them and possibility to unlink
  if ($selected_facets or $not_content_types or $deselected_facets or $deselected_paths or $types or $typegroups) {
    ?>
    <div id="filter" class="facet">
      <h2 title="<?php echo t('Filter criterias'); ?>">
        <?php echo t("Selected filters"); ?>
      </h2>
      <ul id="selected" class="no-bullet">
        <?php

        foreach ($deselected_paths as $deselected_path) {
          print '<li><a onclick="waiting_on();" title="' . t('Remove filter') . '" href="' . buildurl_delvalue($params, 'NOT_path', $deselected_path, 's', 1) . '">(&times;)</a> NOT in ' . htmlspecialchars($deselected_path) . '</li>';
        }


        foreach ($selected_facets as $selected_facet => $facetvalue_array) {
          foreach ($facetvalue_array as $facet_value) {
            print '<li><a onclick="waiting_on();" title="' . t('Remove filter') . '" href="' . buildurl_delvalue($params, $selected_facet, $facet_value, 's', 1) . '">(&times;)</a> ' . $cfg['facets'][$selected_facet]['label'] . ': ' . htmlspecialchars($facet_value) . '</li>';
          }
        }

        foreach ($deselected_facets as $deselected_facet => $facetvalue_array) {
          foreach ($facetvalue_array as $facet_value) {
            print '<li><a onclick="waiting_on();" title="' . t('Remove filter') . '" href="' . buildurl_delvalue($params, 'NOT_' . $deselected_facet, $facet_value, 's', 1) . '">(&times;)</a> NOT ' . $cfg['facets'][$deselected_facet]['label'] . ': ' . htmlspecialchars($facet_value) . '</li>';
          }
        }


        foreach ($types as $type) {
          print '<li><a onclick="waiting_on();" title="' . t('Remove filter') . '" href="' . buildurl($params, 'type', FALSE, 's', 1) . '">(&times;)</a> Typ: ' . htmlspecialchars($type) . '</li>';
        }

        foreach ($not_content_types as $not_content_type) {
          print '<li><a onclick="waiting_on();" title="' . t('Remove filter') . '" href="' . buildurl_delvalue($params, 'NOT_content_type', $not_content_type, 's', 1) . '">(&times;)</a> NOT ' . htmlspecialchars($not_content_type) . '</li>';
        }


        foreach ($typegroups as $typegroup) {
          print '<li><a onclick="waiting_on();" title="' . t('Remove filter') . '" href="' . buildurl($params, 'typegroup', FALSE, 's', 1) . '">(&times;)</a> Form: ' . htmlspecialchars($mimetypes[$typegroup]['name']) . '</li>';
        }
        ?>
      </ul>
    </div>
    <?php
  }
  ?>

  <?php
  if ($path) {
    ?>
    <div id="path">
      <h2>
        <?php echo t('Path'); ?>
      </h2>

      <?php
      $trimmedpath = trim($path, '/');

      $paths = explode('/', $trimmedpath);

      print '<ul><li><a onclick="waiting_on();" href="' . buildurl($params, "path", '', 's', 1) . '">' . t("All paths") . '</a>';

      $fullpath = '';
      for ($i = 0; $i < count($paths) - 1; $i++) {
        $fullpath .= '/' . $paths[$i];
        echo '<ul><li><a onclick="waiting_on();" href="' . buildurl($params, "path", $fullpath, 's', 1) . '">' . $paths[$i] . '</a>' . "\n";
      }

      echo '<ul><li><b>' . htmlspecialchars($paths[count($paths) - 1]) . '</b></li></ul>';

      for ($i = 0; $i < count($paths) - 1; $i++) {
        echo '</li></ul>' . "\n";
      }

      ?>
    </div>
    <?php
  } // if path
  ?>

  <?php

  if (isset($results->facet_counts->facet_fields->$pathfacet)) {
    if ($cfg['debug']) {
      print 'path: ' . $path;
      print 'pathfacet: ' . $pathfacet . '<br>';
      print_r($results->facet_counts->facet_fields->$pathfacet);
    }
    ?>

    <div id="sub" class="facet">
      <h2>
        <?php if ($path) {
          print t('Subpaths');
        }
        else {
          print t('Paths');
        } ?>
      </h2>
      <ul class="no-bullet">
        <?php
        foreach ($results->facet_counts->facet_fields->$pathfacet as $subpath => $count) {

          $fullpath = $path . '/' . $subpath;

          print '<li><a onclick="waiting_on();" href="' . buildurl($params, "path", $fullpath, 's', 1) . '">' . htmlspecialchars($subpath) . '</a> (' . $count . ') <a onclick="waiting_on();" href="' . buildurl_addvalue($params, "NOT_path", $fullpath, 's', 1) . '">-</a></li>';
        }
        ?>
      </ul>
    </div>

    <?php
  } // if subpaths


  ?>

  <div id="file_modified_dt" class="facet">
    <h2>
      <?php echo t('File date'); ?>
    </h2>

    <?php // navigation up

    if ($upzoom_link) {
      print '<a onclick="waiting_on();" href="' . $upzoom_link . '">' . $upzoom_label . '</a> &gt; ' . $date_label;
    }

    ?>
    <ul class="no-bullet">

      <?php

      // newest first
      if ($zoom = 'years') {
        $datevaluessorted = array_reverse($datevalues);
      }
      else {
        $datavaluessorted = $datavalues;
      }

      foreach ($datevaluessorted as $value) {

        if ($value['count'] > 0) {
          print '<li><a onclick="waiting_on();" href="' . $value['link'] . '">' . $value['label'] . '</a> (' . $value['count'] . ')</li>';
        }
      }


      ?>
    </ul>

  </div>

  <?php


  // Print all configurated facets
  foreach ($cfg['facets'] as $facet => $facet_config) {

    if ($cfg['debug']) {
      print ($facet) . '<br />';
      print_r($facet_config);
    }

    print_facet($results, $facet, t($facet_config['label']), $facets_limit);
  }
  ?>

</div>
