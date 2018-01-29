<?php
//
// View named entities
//
?>

<div id="entities" >

<?php
  // Print all configurated facets
  foreach ($cfg['facets'] as $facet => $facet_config):

    if ( !in_array($facet, $exclude_entities) ): ?>

      <div class="small-12 medium-6 columns">

        <?php print_facet($results, $facet, t($facet_config['label']), $facets_limit, $view='entities'); ?>

      </div>

    <?php endif; ?>

  <?php endforeach; ?>

</div>
