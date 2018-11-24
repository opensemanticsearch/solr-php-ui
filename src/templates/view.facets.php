<div id="facets">
  <?php

  // if active filters, show them and possibility to disable
  if ($selected_facets or $deselected_facets) {
    ?>
    <div id="filter" class="facet">
      <h2 title="<?php echo t('Filter criterias'); ?>">
        <?php echo t("Selected filters"); ?>
      </h2>
      <ul id="selected" class="no-bullet">
        <?php

			foreach ($selected_facets as $selected_facet => $facetvalue_array) {
				foreach ($facetvalue_array as $facet_value) {


					if (isset($cfg['facets'][$selected_facet]['tree']) && $cfg['facets'][$selected_facet]['tree']==true) {

				      $trimmedpath = trim($facet_value, '/');

				      $paths = explode('/', $trimmedpath);

						print '<a onclick="waiting_on();" title="' . t('Remove filter') . '" href="' . buildurl_delvalue($params, $selected_facet, $facet_value, 's', 1) . '">(&times;)</a> ' . $cfg['facets'][$selected_facet]['label'] . ': <ul>';

				      $fullpath = '';
      				for ($i = 0; $i < count($paths) - 1; $i++) {
							$fullpath .= '/' . $paths[$i];
							echo '<ul><li><a onclick="waiting_on();" href="' . buildurl($params, $selected_facet, array($fullpath), 's', 1) . '">' . $paths[$i] . '</a>' . "\n";
						}

						echo '<ul><li><b>' . htmlspecialchars($paths[count($paths) - 1]) . '</b></li></ul>';

				      for ($i = 0; $i < count($paths) - 1; $i++) {
        					echo '</li></ul>' . "\n";
				      }
				      echo '</ul>';

					} else {          	
            		print '<li><a onclick="waiting_on();" title="' . t('Remove filter') . '" href="' . buildurl_delvalue($params, $selected_facet, $facet_value, 's', 1) . '">(&times;)</a> ' . $cfg['facets'][$selected_facet]['label'] . ': ' . htmlspecialchars($facet_value) . '</li>';
					}
          }
        }

        foreach ($deselected_facets as $deselected_facet => $facetvalue_array) {
          foreach ($facetvalue_array as $facet_value) {
            print '<li><a onclick="waiting_on();" title="' . t('Remove filter') . '" href="' . buildurl_delvalue($params, 'NOT_' . $deselected_facet, $facet_value, 's', 1) . '">(&times;)</a> NOT ' . $cfg['facets'][$deselected_facet]['label'] . ': ' . htmlspecialchars($facet_value) . '</li>';
          }
        }

        ?>
      </ul>
    </div>
    <?php
  }
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

    if ( !in_array($facet, $exclude_facets) ) {

		if ( isset($facet_config['pathfacet']) ) {
      	print_facet($results, $facet_config['pathfacet'], t($facet_config['label']), $facets_limit, 'list', $facet, $facet_config['path']);
      } else {
      	print_facet($results, $facet, t($facet_config['label']), $facets_limit);
      }

    }

  }
  ?>

</div>
