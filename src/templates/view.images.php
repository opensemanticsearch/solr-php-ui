<?php
// Standard view
//
// Show results as images
?>

<div id="results" class="row">
  <ul class="small-block-grid-1 medium-block-grid-2 large-block-grid-3">

    <?php foreach ($results->response->docs as $doc):

      // URI
      if (isset($doc->container_s)) {
        $id = $doc->container_s;
      }
      else {
        $id = $doc->id;
      }

      $uri_label = $id;
      $uri_tip = FALSE;

      // if file:// then only filename
      if (strpos($id, 'file://') === 0) {
        $uri_label = htmlspecialchars(basename($id));
        // for tooptip remove file:// from beginning
        $uri_tip = htmlspecialchars(substr($id, 7));
      }

      // Author
      $author = htmlspecialchars($doc->author_s);

      // Title
      $title = FALSE;

      if (!empty($doc->title)) {
        $title = htmlspecialchars($doc->title);
      }

      // Type
      $type = $doc->content_type;

      // Modified date
      $datetime = FALSE;
      if (isset($doc->file_modified_dt)) {
        $datetime = $doc->file_modified_dt;
      }
      elseif (isset($doc->last_modified)) {
        $datetime = $doc->last_modified;
      }

      // File size
      $file_size = 0;
      $file_size_txt = '';
      if (isset($doc->file_size_i)) {
        $file_size = $doc->file_size_i;
        $file_size_txt = filesize_formatted($file_size);
      }

      // Snippet
      if (isset($results->highlighting->$id->content)) {
        $snippet = $results->highlighting->$id->content[0];
      }
      else {
        $snippet = $doc->content;
        if (strlen($snippet) > $snippetsize) {
          $snippet = substr($snippet, 0, $snippetsize) . "...";
          $snippet = htmlspecialchars($snippet);
        }
      } ?>

      <li>
        <div class="image">
          <a target="_blank" href="<?= $id ?>">
            <img width="200" src="<?= $id ?>" <?= $title ? 'title="' . $title . '"' : '' ?> />
          </a>
        </div>

        <div class="row">
          <div class="date small-8 columns"><?= $datetime ?></div>
          <div class="size small-4 columns"><?= $file_size_txt ?></div>
        </div>

        <?php if ($author): ?>
          <div class="author">$author</div>'
        <?php endif; ?>

        <div class="title imagelist">
          <a href="<?= $id ?>"><h2><?= $title ? $title : $uri_label ?></h2></a>
        </div>

        <div class="snippet">
          <?= $snippet ?>
        </div>

        <div class="commands">

          <?php if ($uri_tip): ?>
            <span data-tooltip class="has-tip" title="<?= $uri_tip ?>">
              <a href="<?= $id ?>"><?= t('open'); ?></a>
            </span>
          <?php else: ?>
            <a href="<?= $id ?>"><?= t('open'); ?></a>
          <?php endif; ?>

          <?php if ($cfg['metadata']['server']): ?>
            | <a title="<?= t('meta description'); ?>"
                 href="<?= get_metadata_uri($cfg['metadata']['server'], $id); ?>"><?= t('meta'); ?></a>
          <?php endif; ?>
          | <?= '<a href="preview.php?id=' . urlencode($id) . '">' . t('Preview') . '</a>'; ?>

        </div>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
