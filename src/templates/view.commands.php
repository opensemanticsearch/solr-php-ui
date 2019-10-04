        <div class="commands">
          <a href="<?= $uri ?>"><?= t('open'); ?></a>
          <?php if ($cfg['metadata']['server']): ?>
            | <a title="<?= t('meta description'); ?>"
                 href="<?= get_metadata_uri($cfg['metadata']['server'], $uri_unmasked); ?>"><?= t('meta'); ?></a>
          <?php endif; ?>
          
          <?php if ($cfg['hypothesis']['server']): ?>
            | <a title="<?= t('meta description'); ?>"
                 href="<?= get_metadata_uri($cfg['hypothesis']['server'], $uri_unmasked); ?>"><?= t('Annotate visual'); ?></a>
          <?php endif; ?>

          | <a
            href="?view=preview&q=<?= rawurlencode('id:"' . $uri_unmasked . '"') ?>"><?= t('Preview') ?></a>
        </div>
