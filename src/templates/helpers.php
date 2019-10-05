<?php

function format_title($title_txt, $default=FALSE) {
    if (!isset($title_txt) || empty($title_txt))
        return $default;
    if(is_array($title_txt)) {
        $title_txt = implode(", ", $title_txt);
    }
    return htmlspecialchars($title_txt);
}


// convert large sizes (in bytes) to better readable unit
function filesize_formatted($size)
{
	$units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	$power = $size > 0 ? floor(log($size, 1024)) : 0;
	return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}


define('SNIPPETS_OPEN', 3);

function get_facets($result_nr, $doc, $facet_cfg /*=$cfg['facets']*/) {
  $results = [];

  // All configured facets, but for each result individually.
  foreach ($facet_cfg as $field => $facet_config) {
    $f_cfg = $facet_cfg[$field];
    if ($field !== '_text_' && isset($doc->$field) && $f_cfg['snippets_enabled']) {
      $id = $result_nr . $field;
      $result = [
        'facet' => $field,
        'class' => [$field, 'facet'],
        'name' => $f_cfg['label'],
        'title' => t('Named entities or tags'),
        'values' => [],
        'more-values' => [],
      ];

      // Fetch all the facets into an array, with their output properties.
      // Treat non-array values as an array of 1 for simplicity but keep a note
      // as well.
      $values = [];
      if (is_array($doc->$field)) {
        $result['value-type'] = 'multivalue';
        $count = 0;
        $values = [];
        foreach ($doc->{$field} as $value) {
          $values[] = $value;
        }
        $uniq = array_unique($values, SORT_STRING);
        $values = [];
        foreach ($uniq as $value) {
          $values[] = [
            'value' => htmlspecialchars(trim($value)),
            'class' => ['facet-value', ($count & 1) ? 'odd' : 'even'],
          ];
          $count++;
        }
      }
      else {
        $result['value-type'] = 'singlevalue';
        $values[] = [
          'value' => htmlspecialchars(trim($doc->{$field})),
          'class' => ['facet-value'],
        ];
      }

      // Split the facet values up at the 'more' limit.
      $count = count($values);
      // TODO: use coalesce ?? here?
      $max_open = isset($f_cfg['snippets_limit']) ? $f_cfg['snippets_limit'] : SNIPPETS_OPEN;
      $open = array_slice($values, 0, $max_open, TRUE);
      $rest = array_slice($values, $max_open, NULL, TRUE);

      // Record those to display and those to hide separately.
      $result['values'] = $open;
      $result['more-values'] = $rest;

      // If there are hidden values, create a button to show them.
      if (!empty($rest)) {
        $more_id = $id . '#more-snippets';
        $btn_id = $id . '#more-snippets-button';
        $result['more'] = [
          'more_id' => $more_id,
          'btn_id' => $btn_id,
          'href' => '#' . $id,
          'onclick' => "document.getElementById('$more_id').style.display = 'block'; document.getElementById('$btn_id').style.display = 'none';",
          'title' => 'Show all ' . $count . ' ' . $f_cfg['label'],
        ];
      }
      $results[$field] = $result;
    }
  }
  return $results;
}


function get_snippets($result_nr, $snippets) {
  $id = $result_nr;
  $result = [
    'class' => [],
    'values' => [],
    'more-values' => [],
  ];
  $count = 0;
  $values = [];
  foreach ($snippets as $value) {
    $values[] = [
      'value' => trim($value),
      'class' => ['snip', ($count & 1) ? 'odd' : 'even'],
    ];
    $count++;
  }

  // Split the snippets values up at the 'more' limit.
  $count = count($values);
  $open = array_slice($values, 0, SNIPPETS_OPEN, TRUE);
  $rest = array_slice($values, SNIPPETS_OPEN, NULL, TRUE);

  // Record those to display and those to hide separately.
  $result['values'] = $open;
  $result['more-values'] = $rest;

  // If there are hidden values, create a button to show them.
  if (!empty($rest)) {
    $more_id = $id . '#more-snippets';
    $btn_id = $id . '#more-snippets-button';
    $result['more'] = [
      'more_id' => $more_id,
      'btn_id' => $btn_id,
      'href' => '#' . $id,
      'onclick' => "document.getElementById('$more_id').style.display = 'block'; document.getElementById('$btn_id').style.display = 'none';",
      'title' => t('Show all ' . $count . ' snippets'),
    ];
  }
  return $result;
}


function get_urls($id, $container) {

		# url for display
		$url_display = htmlspecialchars($id);
		$url_display_basename = htmlspecialchars(basename($id));

		# url for preview
		$url_preview = $id;

		# url for linking/opening file and annotation / url of container for displaying
		if ($container) {
	      $url_container_display = htmlspecialchars($container);
	      $url_container_display_basename = htmlspecialchars(basename($container));
	      $url_openfile = htmlspecialchars($container);
	      $url_annotation = $container;
		} else {
			$url_container_display = FALSE;
	      $url_container_display_basename = FALSE;
	      $url_openfile = htmlspecialchars($id);
	      $url_annotation = $id;
		}
		      
      return array($url_display, $url_display_basename, $url_preview, $url_openfile, $url_annotation, $url_container_display, $url_container_display_basename);
}

?>
