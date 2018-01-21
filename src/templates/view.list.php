<?php
// Standard view: a list.
//

// Number of snippets initially displayed.
$snippets_open = 3;
?>

<div id="results" class="row">
  <ul class="no-bullet">
    <?php
    $result_nr = 0;
    foreach ($results->response->docs as $doc):
      $result_nr++;
      $id = $doc->id;

      // Type
      $type = $doc->content_type;

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


      // Author
      $author = htmlspecialchars($doc->author_s);

      // Title
      // $title = t('No title');
      $title = $uri_label;
      if (!empty($doc->title)) {
        $title = htmlspecialchars($doc->title);
      }

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
      if (isset($doc->file_size_i)) {
        $file_size = $doc->file_size_i;
        $file_size_txt = filesize_formatted($file_size);
      }

      // Snippet
      //print_r($results->highlighting->$id);

      $snippets = array();

      if (isset($results->highlighting->$id->content)) {
        $snippets = $results->highlighting->$id->content;
      }

      foreach ($cfg['languages'] as $language) {
        $language_specific_fieldname = 'content_txt_' . $language;
        if (isset($results->highlighting->$id->$language_specific_fieldname)) {
          $snippets = $results->highlighting->$id->$language_specific_fieldname;
        }
      }

      if (count($snippets) === 0 && isset($doc->content)) {
        // if no snippets available, use content as snippet
        $snippets = array($doc->content);
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

        <div class="snippets">
          <?php if ($author): ?>
            <div class="author"><?= $author ?></div>
          <?php endif; ?>
          <ul>
            <?php $snippet_number = 0; ?>
            <?php foreach ($snippets as $snippet): ?>
            <?php if (++$snippet_number == ($snippets_open + 1)): ?>
          </ul>
          <ul class="more-snippets" id="<?= $result_nr ?>#more-snippets">
            <?php endif; ?>
            <li class="snippet"><?= $snippet ?></li>
            <?php endforeach; ?>
          </ul>
          <?php if ($snippet_number > $snippets_open): ?>
            <a class="tiny button" id="<?= $result_nr ?>#more-snippets-button"
               href="#<?= $result_nr ?>"
               onClick="
                 document.getElementById('<?= $result_nr ?>#more-snippets').style.display = 'block';
                 document.getElementById('<?= $result_nr ?>#more-snippets-button').style.display = 'none';
                 "><?= t("Show all " . $snippet_number . " snippets") ?></a>
          <?php endif; ?>
        </div>

        <?php
        $first_facet = TRUE;
        // Print all configured facets, but the field of result, not the facet of all results.
        foreach ($cfg['facets'] as $field => $facet_config): ?>
          <?php $first_value = TRUE; ?>
          <?php if ($field != '_text_' and $cfg['facets'][$field]['snippets_enabled']): ?>
            <?php if (isset($doc->$field)): ?>
              <?= ($first_facet) ? '' : '; ' ?>
              <?php $first_facet = FALSE; ?>
            <span class="<?= $field ?>">
              <span
                title="<?= t("Extracted named entities or annotated tags") ?>">
                  <?= $cfg['facets'][$field]['label'] ?>:
                </span>
              <?php if (is_array($doc->$field)): ?>
                <?php $entities_open = $cfg['facets'][$field]['snippets_limit']; ?>
                <?php $entity_number = 0; ?>
                <?php foreach ($doc->$field as $value): ?>
                  <?php if (++$entity_number === ($entities_open + 1)): ?>
                    </span>
                    <span class="more-snippets" id="<?= $result_nr ?><?= $field ?>#more-snippets">
                  <?php endif; ?>
                    <?= ($entity_number > 1) ? ', ' : '' ?>
                    <span
                    class="facet-value"><?= htmlspecialchars($value) ?></span>
                <?php endforeach; ?>
		</span>

                  <?php if ($entity_number > $entities_open): ?>
                  <a class="tiny button"
                     id="<?= $result_nr ?><?= $field ?>#more-snippets-button"
                     href="#<?= $result_nr ?><?= $field ?>"
                     onClick="
                       document.getElementById('<?= $result_nr ?><?= $field ?>#more-snippets').style.display = 'block';
                       document.getElementById('<?= $result_nr ?><?= $field ?>#more-snippets-button').style.display = 'none';
                       "
                     title="Show all "<?= $entity_number ?> <?= $cfg['facets'][$field]['label'] ?>"><?= t('More') ?>
                    </a>
                <?php endif; ?>
              <?php else: ?>
                <?= ($first_value) ? '' : ', ' ?>
                <span
                  class="facet-value"><?= htmlspecialchars($doc->$field) ?></span>
                <?php $first_value = FALSE; ?>
              <?php endif; ?>
              </span>
            <?php endif; ?>
          <?php endif; ?>
        <?php endforeach; ?>

        <div class="commands">
          <a href="<?= $uri ?>"><?= t('open'); ?></a>
          <?php if ($cfg['metadata']['server']): ?>
            | <a title="<?= t('meta description'); ?>"
                 href="<?= get_metadata_uri($cfg['metadata']['server'], $uri_unmasked); ?>"><?= t('meta'); ?></a>
          <?php endif; ?>
          | <a
            href="preview.php?id='<?= urlencode($uri_unmasked) ?>"><?= t('Preview') ?></a>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
</div>

