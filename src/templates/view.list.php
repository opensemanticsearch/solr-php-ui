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

      // Type
      $type = $doc->content_type_ss;

      // URI

      // if part of container like zip, link to container file
      // if PDF page URI to Deeplink
      // since PDF Reader can open deep links
      if (isset($doc->container_s) and $type != 'PDF page') {
        $uri = $doc->container_s;
        $deepid = $id;

      }
      else {
        $uri = $id;
        $deepid = FALSE;
      }

      $uri_label = $uri;
      $uri_tip = FALSE;

      // if file:// then only filename
      if (strpos($uri, "file://") == 0) {
        $uri_label = basename($uri);

        // for tooptip remove file:// from beginning
        $uri_tip = substr($uri, 7);
        $uri_tip = htmlspecialchars($uri_tip);

      }

      if ($deepid) {
        $deep_uri_label = $deepid;
        $deep_uri_label = htmlspecialchars($deep_uri_label);

        $deep_uri_tip = FALSE;
        // if file:// then only filename
        if (strpos($deepid, "file://") == 0) {
          $deep_uri_label = basename($deepid);
          $deep_uri_label = htmlspecialchars($deep_uri_label);

          // for tooltip remove file:// from beginning
          $deep_uri_tip = substr($deepid, 7);
          $deep_uri_tip = htmlspecialchars($deep_uri_tip);

        }
      }

      $uri_unmasked = $uri;
      $uri = htmlspecialchars($uri);
      $uri_label = htmlspecialchars($uri_label);


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
      //print_r($results->highlighting->$id);

      $snippets = array();

      if (isset($results->highlighting->$id->content_txt)) {
        $snippets = $results->highlighting->$id->content_txt;
      }

      foreach ($cfg['languages'] as $language) {
        $language_specific_fieldname = 'content_txt_' . $language;
        if (isset($results->highlighting->$id->$language_specific_fieldname)) {
          $snippets = $results->highlighting->$id->$language_specific_fieldname;
        }
      }

      if (count($snippets) === 0) {
        if (isset($results->highlighting->$id->ocr_t)) {
          $snippets = $results->highlighting->$id->ocr_t;
        }
		}

      if (count($snippets) === 0 && isset($doc->content_txt)) {
        // if no snippets available, use content as snippet
        $snippets = array($doc->content_txt);
        // and cut it to snippet size
        if (strlen($snippets[0]) > $cfg['snippetsize']) {
          $snippets[0] = substr($snippets[0], 0, $cfg['snippetsize']) . "...";
        }
      }
      ?>
      <li id="<?= $result_nr ?>">
        <div class="title"><a class="title" href="<?= $uri ?>"><?= $title ?></a>
        </div>
        <div class="date"><?= $datetime ?></div>
        <div>
          <span class="uri">
            <?php if ($deepid): ?>
              <?php if ($deep_uri_tip): ?>
                <span data-tooltip class="has-tip" title="<?= $deep_uri_tip ?>">
              <?php endif; ?>
              <?= $deep_uri_label ?>
              <?php if ($deep_uri_tip): ?>
                </span>
              <?php endif; ?>
              in
            <?php endif; ?>
            <?php if ($uri_tip): ?>
            <span data-tooltip class="has-tip" title="<?= $uri_tip ?>">
            <?php endif; ?>
            <?= $uri_label ?>
            <?php if ($uri_tip): ?>
              </span>
          <?php endif; ?>
          </span>
          <?php if ($file_size_txt): ?>
            <span class="size">(<?= $file_size_txt ?>)</span>
          <?php endif; ?>
        </div>


        <?php $snippets = get_snippets($result_nr, $snippets); ?>
        <div class="snippets">

          <?php if ($authors): ?>
            <div class="author"><?= htmlspecialchars(implode(", ", $authors)) ?></div>
          <?php endif; ?>

          <ul class="snips">
            <?php foreach ($snippets['values'] as $snip): ?>
              <li class="<?= implode(' ', $snip['class']) ?>"><?= $snip['value'] ?></li>
            <?php endforeach; ?>
          </ul>

          <?php if (!empty($snippets['more-values'])): ?>
            <a class="tiny button" id="<?= $snippets['more']['btn_id'] ?>"
               href="<?= $snippets['more']['href'] ?>"
               onClick="<?= $snippets['more']['onclick'] ?>"
               title="<?= $snippets['more']['title'] ?>"><?= t('More') ?></a>
            <ul id="<?= $snippets['more']['more_id'] ?>" class="more-snips <?= implode(' ', $snippets['class']) ?>">
              <?php foreach ($snippets['more-values'] as $snip): ?>
                <li class="<?= implode(' ', $snip['class']) ?>"><?= $snip['value'] ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>

        </div>


        <?php $facets = get_facets($result_nr, $doc, $cfg['facets']); ?>
        <span class="facets">
          <?php foreach ($facets as $field => $facet): ?>

            <span class="<?= implode(' ', $facet['class']) ?>">
            <span class="facet-name"
                  title="<?= $facet['title'] ?>"><?= $facet['name'] ?></span>

              <?php foreach ($facet['values'] as $value): ?>
                <span class="<?= implode(' ', $value['class']) ?>"><?= $value['value'] ?></span>
              <?php endforeach; ?>

              <?php if (!empty($facet['more-values'])): ?>
                <a class="tiny button" id="<?= $facet['more']['btn_id'] ?>"
                   href="<?= $facet['more']['href'] ?>"
                   onClick="<?= $facet['more']['onclick'] ?>"
                   title="<?= $facet['more']['title'] ?>"><?= t('More') ?></a>
                <span class="more-values" id="<?= $facet['more']['more_id'] ?>">
                  <?php foreach ($facet['more-values'] as $value): ?>
                    <span class="<?= implode(' ', $value['class']) ?>"><?= $value['value'] ?></span>
                  <?php endforeach; ?>
                </span>
              <?php endif; // more ?>
          </span>

          <?php endforeach; // facet
          ?>
        </span>

        <?php
          include 'templates/view.commands.php';
        ?>

      </li>
    <?php endforeach; ?>
  </ul>
</div>

