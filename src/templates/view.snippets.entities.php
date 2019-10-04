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
