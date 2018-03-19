<?php
//
// View map
//
?>

<div id="map" >
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link rel="shortcut icon" type="image/x-icon" href="docs/images/favicon.ico" />

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css" integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js" integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw==" crossorigin=""></script>


<div id="mapid" style="width: 700px; height: 400px;"></div>

<script>

	var mymap = L.map('mapid').setView([48, 10    ], 5);

	L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
		maxZoom: 18,
		attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
			'<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
			'Imagery © <a href="http://mapbox.com">Mapbox</a>',
		id: 'mapbox.streets'
	}).addTo(mymap);



	var popup = L.popup();

	function onMapClick(e) {
		popup
			.setLatLng(e.latlng)
			.setContent("You clicked the map at " + e.latlng.toString())
			.openOn(mymap);
	}

	mymap.on('click', onMapClick);

<?php
	foreach ($results->facet_counts->facet_fields->location_wkt_ss as $facet => $count) {

		$title = $count . " documents found in which this location occurs";
		$coor = $facet;

		// change WKT format "Point(x y)" to "x,y"

		$coor = str_replace('Point(', '', $coor);
		$coor = str_replace(')', '', $coor);
		$latlon = explode(" ", $coor);
		$lat = $latlon[0];
		$lon = $latlon[1];
		$uri = buildurl_addvalue($params, 'location_wkt_ss', $facet, 'view', null, 's', 1);
		$uri_entities = buildurl_addvalue($params, 'location_wkt_ss', $facet, 'view', 'entities', 's', 1);

		$title = $count . ' document(s) found related to this location within search results of your search query<br>';
		$description = '<a href="' . $uri . '">Show & analyze</a> the ' . $count . ' found document(s)</a><br /><br /><a href="' . $uri_entities . '">Show entities</a> (people, organizations, ...) which occur in document(s) in which this location occurs';
?>


	L.circle([<?= $lon ?>,<?= $lat ?>], 5000, {
		color: 'red',
		fillColor: '#f03',
		fillOpacity: 0.5
	}).addTo(mymap).bindPopup('<b><?= $title ?></b><br /><?= $description ?>');


<?php
	}
?>

</script>
</div>
