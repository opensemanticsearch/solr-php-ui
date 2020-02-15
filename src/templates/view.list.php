<?php
// Standard view: a list.
//

// Number of snippets initially displayed.
require_once(__DIR__ . '/helpers.php');

?>

<div id="results" class="row">
  <ul class="no-bullet">
    <?php
    $result_nr = 0;
    foreach ($results->response->docs as $doc):
      $result_nr++;
      $id = $doc->id;

		$container = isset($doc->container_s) ? $doc->container_s : NULL;
		list ($url_display, $url_display_basename, $url_preview, $url_openfile, $url_annotation, $url_container_display, $url_container_display_basename) = get_urls($doc->id, $container);

		$url_prioritize = NULL;

		if ($cfg['etl_status'] && $count_open_etl_tasks_extraction > -1) {
	      if (isset($doc->etl_file_b) && !isset($doc->etl_enhance_extract_text_tika_server_b)) {
				$url_prioritize = "/search-apps/files/prioritize?url=" . rawurlencode($id);
			}
		}
		
		// Authors
		if (is_array($doc->author_ss)) {
			$authors = $doc->author_ss;
		} else {
			$authors = array($doc->author_ss);
		}
		
      // Title
      $title = format_title($doc->title_txt, $url_display_basename);

      // Modified date
      $datetime = FALSE;
      if (isset($doc->file_modified_dt)) {
        $datetime = $doc->file_modified_dt;
      }
      elseif (isset($doc->last_modified)) {
        $datetime = $doc->last_modified;
      }

      $file_size = 0;
      $file_size_formated = '';
      // File size
      $file_size_field = 'Content-Length_i';
      if (isset($doc->$file_size_field)) {
        $file_size = $doc->$file_size_field;
        $file_size_formated = filesize_formatted($file_size);
      }

 
      ?>
      <li id="<?= $result_nr ?>">
        <div class="title"><a class="title" href="<?= $url_openfile ?>"><?= $title ?></a>
        </div>
        <div class="date"><?= $datetime ?></div>
        <div>
          <?php
            include 'templates/view.url.php';
          ?>

          <?php if ($file_size_formated): ?>
            <span class="size">(<?= $file_size_formated ?>)</span>
          <?php endif; ?>
        </div>


        <div class="snippets">

          <?php if ($authors): ?>
            <div class="author"><?= htmlspecialchars(implode(", ", $authors)) ?></div>
          <?php endif; ?>

          <?php
            include 'templates/view.snippets.text.php';
          ?>
        </div>

        <span class="facets">
        <?php
          $facets = get_facets($result_nr, $doc, $cfg['facets']);
          include 'templates/view.snippets.entities.php';
        ?>

        </span>

        <?php
          include 'templates/view.commands.php';
        ?>

      </li>
    <?php endforeach; ?>
  </ul>
</div>

