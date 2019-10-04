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
