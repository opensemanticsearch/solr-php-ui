<?php
// Standard view
//
// Show results as list

require_once(__DIR__ . '/helpers.php');
?>

<div id="results" class="row">

  <ul class="small-block-grid-1 medium-block-grid-2 large-block-grid-2">

    <?php
    $result_nr = 0;
    foreach ($results->response->docs as $doc):
      $result_nr++;

		$container = isset($doc->container_s) ? $doc->container_s : NULL;
		list ($url_display, $url_display_basename, $url_preview, $url_openfile, $url_annotation, $url_container_display, $url_container_display_basenname) = get_urls($doc->id, $container);

		// Authors
		if (is_array($doc->author_ss)) {
			$authors = $doc->author_ss;
		} else {
			$authors = array($doc->author_ss);
		}
		
		
      // Title
      $title = format_title($doc->title_txt, $uri_label);

      // Modified date
      $datetime = FALSE;
      if (isset($doc->file_modified_dt)) {
        $datetime = $doc->file_modified_dt;
      }
      elseif (isset($doc->last_modified)) {
        $datetime = $doc->last_modified;
      }

      $file_size = 0;
      $file_size_txt = '';
      // File size
      $file_size_field = 'Content-Length_i';
      if (isset($doc->$file_size_field)) {
        $file_size = $doc->$file_size_field;
        $file_size_txt = filesize_formatted($file_size);
      }
      
      // Snippet
      if (isset($results->highlighting->$id->content_txt)) {
        $snippet = $results->highlighting->$id->content_txt[0];
      }
      else {
        $snippet = $doc->content_txt;
        if (strlen($snippet) > $snippetsize) {
          $snippet = substr($snippet, 0, $snippetsize) . "...";
          $snippet = htmlspecialchars($snippet);
        }
      }

      ?>
      <li>

        <div class="title">
          <a class="title" target="_blank" href="<?= $url_openfile ?>">
            <?php if ($title) { ?>
              <?= $title ?>
            <?php } ?>

          </a>
        </div>


        <?php
          include 'templates/view.url.php';
        ?>


        <div class="audio">

          <audio controls="controls" preload="none" src="<?= $url_openfile ?>"></audio>

        </div>


        <div class="row">
          <div class="date small-8 columns"><?= $datetime ?></div>
          <div class="size small-4 columns"><?= $file_size_txt ?></div>
        </div>

        <div class="snippet">
          <?php if ($authors): ?>
            <div class="author"><?= htmlspecialchars(implode(", ", $authors)) ?></div>
          <?php endif; ?>
          <?= $snippet ?>
        </div>
        
        <?php
          include 'templates/view.commands.php';
        ?>
        
      </li>
      <?php endforeach; ?>


  </ul>
</div>
