<?php
// Show results as graph
?>
<script src="js/cytoscape/build/cytoscape.min.js"></script>

<script type="text/javascript">

<?php 

function getedges($cfg, $solr, $solrquery, $additionalParameters, $facet_field, $entity) {

	$edges = array();
	
	$solrfacet = addcslashes($facet_field, '+-&|!(){}[]^"~*?:\/ ');
	$solrfacetvalue = addcslashes($entity, '+-&|!(){}[]^"~*?:\/ ');

	
	$mysolrquery = $solrfacet . ':' . $solrfacetvalue;
	// not needed anymore, since we set the (not changing) $solrquery as more performant because cachable filter query
	//$mysolrquery = $solrquery . ' AND ' . $solrfacet . ':' . $solrfacetvalue;
	
	$tempresults = $solr->search($mysolrquery, 0, 0, $additionalParameters);


	foreach ($cfg['facets'] as $facet_field => $facet_config) {

		#print "checking ".$facet_field;
		
		if (isset($tempresults->facet_counts->facet_fields->$facet_field)) {
			if (count(get_object_vars($tempresults->facet_counts->facet_fields->$facet_field)) > 0) {
	
				foreach ($tempresults->facet_counts->facet_fields->$facet_field as $facet => $count) {
					#$facet_field, $facet $facet $count ;
					if ($entity != $facet) {
						$edges[]= array("source"=> $entity, "target"=> $facet, "weight" => $count);
					}
				}
			}
		} // if isset facetfield
	
	}
	
	
	
	return $edges;

}


$elements=array();
$edges = array();
$weight = array();

# since we will do many queries for each facet value set the not changing part from former solr query/search context to performant because cached filter query
$additionalParameters['fq'] = $solrquery;


foreach ($cfg['facets'] as $facet_field => $facet_config) {

	if (isset($results->facet_counts->facet_fields->$facet_field)) {
		if (count(get_object_vars($results->facet_counts->facet_fields->$facet_field)) > 0) {
		
			foreach ($results->facet_counts->facet_fields->$facet_field as $facet => $count) {
				#$facet_field, $facet $count ;
				$entity = $facet;
				//$elements[] = $entity;

				// todo: scale from min (1 px) to max (max configurable)
				$weight[$entity]=bcsqrt($count,0)*10;
				
				$myedges=getedges($cfg, $solr, $solrquery, $additionalParameters, $facet_field, $entity);
			 	$edges = array_merge($edges, $myedges);
			}
		}
	} // if isset facetfield
	
}

foreach ($edges as $edge) {
	if (in_array($edge["source"], $elements)==false) {
		$elements[] = $edge["source"];
	}
}


?>




var elements = {
	      nodes: [
	
<?php

	$first = true;
	foreach ($elements as $element) {

		// if not first entry, print delimiting comma
		if ($first) { $first = false; } else {print ",\n"; }
		
		print "{data:{id:'".str_replace("'", "", $element)."', weight: ".$weight[$element]."}}";
 		 

	}
?>
	      ], 

	      
	  	      edges: [

	  	          <?php 
	  	          	$first = true;
					$i = 0;
	  	        	foreach ($edges as $edge) {
	  	        		$i++;

	  	        		// if not first entry, print delimiting comma
		  	      		if ($first) { $first = false; } else {print ",\n"; }
	  	      		
		  	      		print "{data:{id: 'edge".$i."',source:'".str_replace("'", "", $edge["source"])."', target:'".str_replace("'", "", $edge["target"])."', weight: ".$edge["weight"]." }}";


	  	      	}
	  	      	?>
	  	      		  	      	
	  	        ]
	  	    }


function subnav_deactivate_active(){
	document.getElementById('sub_nav_cose').className = "";
	document.getElementById('sub_nav_circle').className = "";
	document.getElementById('sub_nav_concentric').className = "";
	document.getElementById('sub_nav_grid').className = "";
	document.getElementById('sub_nav_breadthfirst').className = "";
	
}


function view_cose(){

	 subnav_deactivate_active();
	 
	 document.getElementById("sub_nav_cose").className = "active";

	var cy = cytoscape({
		  container: document.getElementById('cy'),
		  
		  style: cytoscape.stylesheet()
		    .selector('node')
		      .css({
		        'content': 'data(id)',
			    'width': 'data(weight)',
				'height': 'data(weight)'
			    
		      })
		    .selector('edge')
		      .css({
		        'target-arrow-shape': 'triangle',
		        'width': 'data(weight)',
		        'line-color': '#ddd',
		        'target-arrow-color': '#ddd'
		      })
		    .selector('.highlighted')
		      .css({
		        'background-color': '#61bffc',
		        'line-color': '#61bffc',
		        'target-arrow-color': '#61bffc',
		        'transition-property': 'background-color, line-color, target-arrow-color',
		        'transition-duration': '0.5s'
		      }),
		  
		  elements: elements,
		  
		  

		  layout: {
			    name: 'cose'
			  }
		  
		});
 
}
	  	    
function view_circle(){

	 subnav_deactivate_active();
	 
	 document.getElementById("sub_nav_circle").className = "active";
	
	var cy = cytoscape({
		  container: document.getElementById('cy'),
		  
		  style: cytoscape.stylesheet()
		    .selector('node')
		      .css({
		        'content': 'data(id)',
			    'width': 'data(weight)',
				'height': 'data(weight)'
			    
		      })
		    .selector('edge')
		      .css({
		        'target-arrow-shape': 'triangle',
		        'width': 'data(weight)',
		        'line-color': '#ddd',
		        'target-arrow-color': '#ddd'
		      })
		    .selector('.highlighted')
		      .css({
		        'background-color': '#61bffc',
		        'line-color': '#61bffc',
		        'target-arrow-color': '#61bffc',
		        'transition-property': 'background-color, line-color, target-arrow-color',
		        'transition-duration': '0.5s'
		      }),
		  
		  elements: elements,
		  
		  

		  layout: {
			    name: 'circle'
			  }
		  
		});
		
}

function view_concentric(){

	 subnav_deactivate_active();
	 
	 document.getElementById("sub_nav_concentric").className = "active";
	
	var cy = cytoscape({
		  container: document.getElementById('cy'),
		  
		  style: cytoscape.stylesheet()
		    .selector('node')
		      .css({
		        'content': 'data(id)',
			    'width': 'data(weight)',
				'height': 'data(weight)'
			    
		      })
		    .selector('edge')
		      .css({
		        'target-arrow-shape': 'triangle',
		        'width': 'data(weight)',
		        'line-color': '#ddd',
		        'target-arrow-color': '#ddd'
		      })
		    .selector('.highlighted')
		      .css({
		        'background-color': '#61bffc',
		        'line-color': '#61bffc',
		        'target-arrow-color': '#61bffc',
		        'transition-property': 'background-color, line-color, target-arrow-color',
		        'transition-duration': '0.5s'
		      }),
		  
		  elements: elements,
		  
		  

		  layout: {
			    name: 'concentric'
			  }
		  
		});
		
}

function view_grid(){

	 subnav_deactivate_active();
	 
	 document.getElementById("sub_nav_grid").className = "active";

	var cy = cytoscape({
		  container: document.getElementById('cy'),
		  
		  style: cytoscape.stylesheet()
		    .selector('node')
		      .css({
		        'content': 'data(id)',
			    'width': 'data(weight)',
				'height': 'data(weight)'
			    
		      })
		    .selector('edge')
		      .css({
		        'target-arrow-shape': 'triangle',
		        'width': 'data(weight)',
		        'line-color': '#ddd',
		        'target-arrow-color': '#ddd'
		      })
		    .selector('.highlighted')
		      .css({
		        'background-color': '#61bffc',
		        'line-color': '#61bffc',
		        'target-arrow-color': '#61bffc',
		        'transition-property': 'background-color, line-color, target-arrow-color',
		        'transition-duration': '0.5s'
		      }),
		  
		  elements: elements,
		  
		  

		  layout: {
			    name: 'grid'
			  }
		  
		});

}

function view_breadthfirst(){

	 subnav_deactivate_active();
	 
	 document.getElementById("sub_nav_breadthfirst").className = "active";

	var cy = cytoscape({
		  container: document.getElementById('cy'),
		  
		  style: cytoscape.stylesheet()
		    .selector('node')
		      .css({
		        'content': 'data(id)',
			    'width': 'data(weight)',
				'height': 'data(weight)'
			    
		      })
		    .selector('edge')
		      .css({
		        'target-arrow-shape': 'triangle',
		        'width': 'data(weight)',
		        'line-color': '#ddd',
		        'target-arrow-color': '#ddd'
		      })
		    .selector('.highlighted')
		      .css({
		        'background-color': '#61bffc',
		        'line-color': '#61bffc',
		        'target-arrow-color': '#61bffc',
		        'transition-property': 'background-color, line-color, target-arrow-color',
		        'transition-duration': '0.5s'
		      }),
		  
		  elements: elements,
		  
		  

		  layout: {
			    name: 'breadthfirst'
			  }
		  
		});

	var options = {
			  name: 'breadthfirst',

			  fit: true, // whether to fit the viewport to the graph
			  directed: false, // whether the tree is directed downwards (or edges can point in any direction if false)
			  padding: 30, // padding on fit
			  circle: false, // put depths in concentric circles if true, put depths top down if false
			  spacingFactor: 1.75, // positive spacing factor, larger => more space between nodes (N.B. n/a if causes overlap)
			  boundingBox: undefined, // constrain layout bounds; { x1, y1, x2, y2 } or { x1, y1, w, h }
			  avoidOverlap: true, // prevents node overlap, may overflow boundingBox if not enough space
			  roots: undefined, // the roots of the trees
			  maximalAdjustments: 0, // how many times to try to position the nodes in a maximal way (i.e. no backtracking)
			  animate: false, // whether to transition the node positions
			  animationDuration: 500, // duration of animation in ms if enabled
			  ready: undefined, // callback on layoutready
			  stop: undefined // callback on layoutstop
			};

			cy.layout( options );
}



$(function(){ // on dom ready

	view_cose();

}); // on dom ready
</script>




<div id="results" class="row">
<div class="small-12 columns">
<dl class="sub-nav">
  <dt>Graph view:</dt>
  <dd id="sub_nav_cose" class="active"><a href="#" onclick="view_cose();">Cose</a></dd>
  <dd id="sub_nav_circle"><a href="#" onclick="view_circle();">Circle</a></dd>
  <dd id="sub_nav_breadthfirst"><a href="#" onclick="view_breadthfirst();">Breadthfirst</a></dd>
  <dd id="sub_nav_concentric"><a href="#" onclick="view_concentric();">Concentric</a></dd>
  <dd id="sub_nav_grid"><a href="#" onclick="view_grid();">Grid</a></dd>
  </dl>
</div>

<div class="small-12 columns" style="height: 120%; width=100%;" id="cy"></div>


    

</div>
