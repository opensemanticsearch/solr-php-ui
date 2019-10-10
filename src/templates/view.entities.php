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

        <?php
        
			if ( isset($facet_config['pathfacet']) ) {
				$path = FALSE;
				if (isset($facet_config['path'])) {
					$path=$facet_config['path'];
				}
	      	print_facet($results, $facet_config['pathfacet'], t($facet_config['label']), $facets_limit, 'entities', $facet, $path);
   	   } else {
      		print_facet($results, $facet, t($facet_config['label']), $facets_limit, 'entities');
      	}

		  ?>
      </div>

    <?php endif; ?>

  <?php endforeach; ?>

</div>
