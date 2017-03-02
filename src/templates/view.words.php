<?php
// Show results as word cloud
?>

<div id="results" class="row">

<div class="row">
<i>Count of documents (total: <?=$total?>) containing this words:</i>
</div>


<div class="small-12 medium-2 columns">
<ol>

<?php

	foreach ($results->facet_counts->facet_fields->_text_ as $facet=>$count) {
		print '<li><a onclick="waiting_on();" href="'.buildurl_addvalue($params,'_text_', $facet, 's',1).'">'.htmlspecialchars($facet).'</a> ('.$count.')</li>';
	}


?>
    </ol>
</div>
    

<div  id="wordcloud" class="small-12 medium-8 columns">
</div>

    
    
<script type="text/javascript">

var words = [

<?php
	$first = true;
 	foreach ($results->facet_counts->facet_fields->_text_ as $word=>$count) {

 		$wordlink = buildurl_addvalue($params, '_text_', $word, 's', 1);
 		 		 
 		// if not first entry, print delimiting comma
 		if ($first) { $first = false; } else {print ",\n"; }

 	
 		print '{ "text": "' . $word . '", "size": ' . $count . ', "link": "'.$wordlink.'" }';
 	}
?>
];

</script>


<script src="d3js/d3.min.js" charset="utf-8"></script>
<script src="d3js/d3.layout.cloud.js"></script>

<script type="text/javascript">

var size = [500, 600];

var fontSize = d3.scale.log().range([10, 30]);

d3.layout.cloud().size(size)
	.words(words)
	.fontSize(function(d) { return fontSize(+d.size); })
	.rotate(function() { return 0 })
	.on("end", draw)
	.start();


function draw(words) {
	d3.select("#wordcloud").append("svg")
	.attr("width", size[0])
	.attr("height", size[1])
	.append("g")
	.attr("transform", "translate(" + (size[0]/2) + "," + (size[1]/2) + ")")
	.selectAll("text")
	.data(words)
	.enter()
	.append("text")
	.style("font-size", function(d) { return d.size + "px"; })
	.attr("text-anchor", "middle")
	.attr("transform", function(d) {
			return "translate(" + [d.x, d.y] + ")rotate(" + d.rotate + ")";
		})
	.attr("onclick", function(d) {return "{waiting_on(); location.href='" + d.link + "';}";} )
	.text(function(d) { return d.text; })
	
  }
  
    

</script>

</div>
