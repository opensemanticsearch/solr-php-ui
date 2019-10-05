<?php
      // Snippets

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

      $snippets = get_snippets($result_nr, $snippets);
      
?>
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
