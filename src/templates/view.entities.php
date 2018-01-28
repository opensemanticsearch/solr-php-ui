<?php

//
// View named entities
//

  // TODO: add entities_enabled and entities_limit to facet config and facet config ui
  $exclude_facets = ['content_type','content_type_group','language_s'];

  // Print all configurated facets
  foreach ($cfg['facets'] as $facet => $facet_config) {

    if ( !in_array($facet, $exclude_facets) ) {
      print_facet($results, $facet, t($facet_config['label']), $facets_limit, $view='entities');
    }

  }

  ?>

