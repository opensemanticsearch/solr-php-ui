        <div class="commands">
          <a href="<?= $url_openfile ?>"><?= t('open'); ?></a>
          <?php if ($cfg['metadata']['server']): ?>
            | <a title="<?= t('meta description'); ?>"
                 href="<?= get_metadata_uri($cfg['metadata']['server'], $url_annotation); ?>"><?= t('meta'); ?></a>
          <?php endif; ?>
          
          <?php if ($cfg['hypothesis']['server']): ?>
            | <a title="<?= t('meta description'); ?>"
                 href="<?= get_metadata_uri($cfg['hypothesis']['server'], $url_annotation); ?>"><?= t('Annotate visual'); ?></a>
          <?php endif; ?>

          | <a
            href="?view=preview&q=<?= rawurlencode('id:"' . $url_preview . '"') ?>"><?= t('Preview') ?></a>
        </div>
