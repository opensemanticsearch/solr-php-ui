<?php

// Setup parameters for graph visualization by Open Semantic Visual Linked Data Graph Explorer

$link_graph = '/search-apps/graph/?q='.$query;
$link_graph .= '&fl=' . implode(',', $graph_fields);

foreach ($cfg['facets'] as $facet => $facet_config) {

   if ( in_array($facet, $graph_fields) ) {
	
		// todo: read from coming facet config graph_limit
		$facetlimit = 50;

		if (isset($facets_limit[$facet])) {
			$facetlimit = $facets_limit[$facet];
		}

		$link_graph .= "&f." . $facet . ".facet.limit=" . $facetlimit;
	}
}
?>

<hr/>


<div class="row">
<div class="column small-12 large-6">

<h3>Graph visualization</h3>

<p>Graph visualization of co-occurences of entities and knowledge graph connections between entities which occur in the found/filtered <?= $total ?> document(s).</p>

<a class= "button" target="_blank" href="<?= $link_graph ?>">Visualize graph starting with following (types and amount/limit of) entities</a>
<p>or set other types of entities and/or amount/limit of starting entities to start with:</p>


</div>
<div class="column small-12 large-6">

<h4>Types of entities (classes) &amp; connections (properties)</h4>

<p>Select types of entities (entity classes) and connections (properties) to query, connect and show in your analysis / visualization:</p>

<p>
<?php

  foreach ($cfg['facets'] as $facet => $facet_config) {

    if ( in_array($facet, $graph_fields) ) {

 		$graph_fields_exclude = implode(',', array_diff($graph_fields, array($facet)));

		$link_graph_fields_exclude = buildurl($params,'graph_fl', $graph_fields_exclude);

      print ("<span class=\"facets facet facet-value " . $facet . "\">" . $facet_config['label'] . "&nbsp;(<a href=\"" . $link_graph_fields_exclude . "\">ignore</a>)</span>" );

    }

  }

?>
</p>


<p><a class="button small" id="more_classes" data-toggler data-toggle="inactive more_classes" data-animate="hinge-in-from-top slide-out-down">Add more ...</a></p>

<div style="display: none;" class="callout" id="inactive" data-toggler data-animate="hinge-in-from-top spin-out">
Ignored (not queried for connections and not shown in the graph) entity types (classes):
<p>
<?php

  foreach ($cfg['facets'] as $facet => $facet_config) {

    // exclude yet active facets from option to add facet
    // and exclude facet phone_ss (maybe same number in different values of different format) since for graph view additional existent facet phone_normalized_ss (same number, independent of format) works better
    if ($facet != 'phone_ss' && !in_array($facet, $graph_fields) ) {

		$graph_fields_include = $graph_fields;
 		$graph_fields_include[] = $facet;
 		
		$link_graph_fields_include = buildurl($params,'graph_fl', implode(',', $graph_fields_include));

      print ("<span class=\"facet-value\"><strike>" . $facet_config['label'] . "</strike>&nbsp;(<a href=\"" . $link_graph_fields_include . "\">include</a>)</span>" );

    }

  }

?>
</p>
  <button class="close-button" aria-label="Dismiss alert" type="button" data-toggle="inactive more_classes">
    <span aria-hidden="true">&times;</span>
  </button>

</div>
</div>

</div>

<h4>Amount of entities for each entity type (limit)</h4>

<p>Limit amount of entities to start with:</p>

<div id="entities" >

<?php
  // Print all configurated facets
  foreach ($cfg['facets'] as $facet => $facet_config):

    if ( in_array($facet, $graph_fields) ): ?>

      <div class="small-12 medium-6 columns">

        <?php print_facet($results, $facet, t($facet_config['label']), $facets_limit, $view='graph'); ?>

      </div>

    <?php endif; ?>

  <?php endforeach; ?>

</div>

<hr/>
<p>Alternate you can query, explore and visualize the documents and entities graph in <a target="_blank" href="<?= $cfg['neo4j_browser'] ?>">Neo4j browser</a> by Cypher queries.</p>

