          <span class="uri">
            <?php if ($url_display): ?>
              <span data-tooltip class="has-tip" title="<?= $url_display ?>">
            <?php endif; ?>
            <?= $url_display_basename ?>
            <?php if ($url_display): ?>
              </span>
            <?php endif; ?>
            <?php if ($url_container_display): ?>
              in
            <span data-tooltip class="has-tip" title="<?= $url_container_display ?>">
            <?= $url_container_display_basename ?>
              </span>
            <?php endif; ?>
          </span>
