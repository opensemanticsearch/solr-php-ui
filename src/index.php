<?php
// SOLR PHP Client UI
//
// PHP-UI of Open Semantic Search - https://opensemanticsearch.org
//
// 2011 - 2020 by Markus Mandalka - https://mandalka.name
// and others (see Git history & issues)
//
// Free Software - License: GPL 3


#
# config defaults
#

# do not change config here, use ./config/config.php!

$cfg['debug'] = false;

$cfg['etl_status'] = false;
$cfg['etl_status_warning'] = false;
$cfg['etl_status_facets'] = array();

if (getenv('SOLR_PHP_UI_SOLR_HOST')) {
    $cfg['solr']['host'] = getenv('SOLR_PHP_UI_SOLR_HOST');
} else {
    $cfg['solr']['host'] = 'localhost';
}

$cfg['solr']['port'] = 8983;
$cfg['solr']['path'] = '/solr';

$cfg['solr']['core'] = 'core1';

$cfg['languages'] = array('en','de','es','fr','hu','nl','pt','it','cz','ro','ru','ar','fa');

// show newest documents, if no query
$cfg['newest_on_empty_query'] = true;

// no link to admin interface
$cfg['solr']['admin']['uri'] = false;

// only metadata option if server set in config
$cfg['metadata']['server'] = false;
$cfg['hypothesis']['server'] = false;
$cfg['neo4j_browser'] = 'http://localhost:7474/browser/';

// size of the snippet
$cfg['snippetsize'] = 300;

// todo: convert labels to t() function or read labels from ontology
// and add to facet config: $lang['en']['facetname'] = 'Facet label';
$cfg['facets'] = array();


$cfg['disable_view_graph'] = false;
$cfg['disable_view_words'] = false;

// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');


// include configs
include 'config/config.php';
include 'config/config.webadmin.php';
include 'config/config.i18n.php';
if (file_exists('config/config.i18n.custom.php')) {
	include 'config/config.i18n.custom.php';
}

// mask special chars but not operators
function mask_query ( $query, $facets=array() ) {

	$unmaskfacets = array('id','title_txt','content_txt','exact','_text_');

	// add configured facets
	foreach ($facets as $facet=>$facetconfig) {
		$unmaskfacets[]= $facet ;
	}

	// Mask special chars for Lucene query, so we can search for that chars, too.
	$query = addcslashes( $query, '&|!{}[]^:\/' );

	// TODO: Mask, if not at start, but unmask if the char ':' is op for facet search.
	foreach ($unmaskfacets as $facet) {

		// Undo masking if ':' is after facet name on beginning of new word or
    // query, because then ':' is a operator to select a facet for query part.

		$query = str_replace($facet.'\:', $facet.':', $query);
		
		// maybe todo:
		// only if startswith (if startswith - facet, too)
		// or after space or (
	}

	// map nicer to read facet name "exact" to facet "_text_"
	$query = str_replace('exact:', '_text_:', $query);
	return $query;
}


function get_uri_help ($language) {
	return 'docs/doc/search/';
}


// Create link with current parameters with one changed parameter.
//
// changing second facet needed if the change of the main parameter will change
// results to reset page number which could be more than first page
function buildurl($params,
                  $facet = NULL, $newvalue = NULL,
                  $facet2 = NULL, $newvalue2 = NULL,
                  $facet3 = NULL, $newvalue3 = NULL,
                  $facet4 = NULL, $newvalue4 = NULL) {

	if ($facet) {
		$params[$facet]=$newvalue;
	}

	if ($facet2) {
		$params[$facet2] = $newvalue2;
	}

	if ($facet3) {
		$params[$facet3] = $newvalue3;
	}

	if ($facet4) {
		$params[$facet4] = $newvalue4;
	}

	// if param NULL, delete it
	foreach ($params as $key=>$value) {
	    if (is_null($value)) { unset($params[$key]); }
	}

	$url = "?".http_build_query($params);

	return $url;
}


function buildurl_addvalue($params, $facet=NULL, $addvalue=NULL, $changefacet=NULL, $newvalue=NULL) {

	if ($facet) {
		$params[$facet][] = $addvalue;
	}

	if ($changefacet) {
		$params[$changefacet] = $newvalue;
	}

	$url = "?" . http_build_query($params);

	// if param NULL, delete it
	foreach ($params as $key=>$value) {
	    if (is_null($value)) { unset($params[$key]); }
	}
	return $url;
}


function buildurl_delvalue($params, $facet=NULL, $delvalue=NULL, $changefacet=NULL, $newvalue=NULL) {

	if ($facet) {
		unset( $params[$facet][array_search($delvalue, $params[$facet])] );
	}

	if ($changefacet) {
		$params[$changefacet] = $newvalue;
	}

	// if param NULL, delete it
	foreach ($params as $key=>$value) {
	    if (is_null($value)) { unset($params[$key]); }
	}

	$url = "?" . http_build_query($params);
	return $url;
}


function buildform($params, $facet=NULL, $newvalue=NULL, $facet2=NULL, $newvalue2=NULL, $facet3=NULL, $newvalue3=NULL, $facet4=NULL, $newvalue4=NULL, $facet5=NULL, $newvalue5=NULL) {

	if ($facet) {
		$params[$facet]=$newvalue;
	}

	if ($facet2) {
		$params[$facet2]=$newvalue2;
	}

	if ($facet3) {
		$params[$facet3]=$newvalue3;
	}

	if ($facet4) {
		$params[$facet4]=$newvalue4;
	}

	if ($facet5) {
		$params[$facet5]=$newvalue5;
	}


	// if param NULL, delete it
	foreach ($params as $key=>$value) {
	    if (is_null($value)) { unset($params[$key]); }
	}

	$form = "";

	foreach ($params as $key=>$value) {

		if (is_array($value)) {
			foreach($value as $postvalue) {
			    $form = $form."<input type=\"hidden\" name=\"".htmlspecialchars($key)."[]\" value=\"".htmlspecialchars($postvalue)."\">";
			}
		} else {
			$form = $form."<input type=\"hidden\" name=\"".htmlspecialchars($key)."\" value=\"".htmlspecialchars($value)."\">";
		}
	}
	return $form;
}


// Get url of metadata page to the given id (filename or uri of the original content)
function get_metadata_uri ($metadata_server, $id) {

	// $url = $metadata_server.md5($id).'?Meta[RefURI]='.urlencode($id); // use md5 hash, because not every cms supports special chars as page id
	$url = $metadata_server.urlencode($id);
	return $url;
}


function date2solrstr($timestamp) {
	$date_str = date('Y-m-d', $timestamp).'T'. date('H:i:s', $timestamp).'Z';

	return $date_str;
}


// values for navigating date facet
function get_datevalues(&$results, $params, $downzoom) {

	$datevalues = array();

	if (empty($results->facet_counts)) {
		return $datevalues;
	}

	foreach ($results->facet_counts->facet_ranges->file_modified_dt->counts as $facet=>$count) {
		$newstart = $facet;

		if ($downzoom=='decade') {
			$newend = $facet . '+10YEARS';

			$value = substr($facet, 0, 4);

		} elseif ($downzoom=='year') {
			$newend = $facet . '+1YEAR';
			$value = substr($facet, 0, 4);

		} elseif ($downzoom=='month') {
			$newend = $facet . '+1MONTH';
			$value = substr($facet, 5, 2);

		} elseif ($downzoom=='day') {
			$newend = $facet . '+1DAY';
			$value = substr($facet, 8, 2);

		} elseif ($downzoom=='hour') {
			$newend = $facet . '+1HOUR';
			$value = substr($facet, 11, 2);

		} elseif ($downzoom=='minute') {
			$newend = $facet . '+1MINUTE';
			$value = substr($facet, 14, 2);

		} else {
			$newend = $facet . '+1YEAR';
			$value = $facet;
		};


		$link = buildurl($params,'start_dt', $newstart, 'end_dt', $newend, 'zoom', $downzoom, 's', false);

		$datevalues[] = array('label'=> $value, 'count' => $count, 'link' => $link);
	}

	return $datevalues;
}


// Get field/property/facet label
function get_label($facet) {
global $cfg;

	// strip Solr data field type suffixes like _ss
	$strip_suffixes = array('_s', '_ss');

	// if field in facet config, use facet label
	if ( isset($cfg['facets'][$facet]) && isset($cfg['facets'][$facet]['label']) ) {
		$label = $cfg['facets'][$facet]['label'];
	} else {
		// else use facet/field name
		$label = $facet;

		// strip suffixes
		foreach ($strip_suffixes as $strip_suffix) {

			   if (substr_compare( $label, $strip_suffix, -strlen( $strip_suffix ) ) === 0) {
				$label = substr($label, 0, -strlen($strip_suffix));
				break;
			}
		}
	}

	return $label;

}

// get the label and uri of a (mixed) field from Solr index which stores both in one field
function get_preflabel_and_uri($preflabel_and_uri) {
	
	// extract lanbel and uri from format "preferred label <uri>"
	if (preg_match('/(.+)\ \<(\S+)\>$/', $preflabel_and_uri, $matches)) {
		$label = $matches[1];
		$uri = $matches[2];
	} else {
		$label = $preflabel_and_uri;
		$uri = FALSE;
	}

	return array('label' => $label, 'uri' => $uri);
}


// Get fields / columns
function get_fields(&$doc, $exclude=array(), $exclude_prefixes=array(), $exclude_suffixes=array(), $exclude_suffixes_if_same_content=array(), $sort=TRUE)
{
	foreach ($doc as $field => $value) {

	  $exclude_field = FALSE;

	  // is field in excluded fields ?
	  if (in_array($field, $exclude)) {
	    $exclude_field = TRUE;
	  }

	  // field name begins with one of excluded prefixes?
	  foreach($exclude_prefixes as $exclude_prefix) {

	   if (strncmp($field, $exclude_prefix, strlen($exclude_prefix)) === 0) {
	     $exclude_field = TRUE;
	   }

	  }

	  // field name ends with one of excluded suffixes?
	  foreach($exclude_suffixes as $exclude_suffix) {

	   if (substr_compare( $field, $exclude_suffix, -strlen( $exclude_suffix ) ) === 0) {
	     $exclude_field = TRUE;
	   }
	  }

	  // field name ends with one of excluded suffixes and has same content in field without suffix?
	  foreach($exclude_suffixes_if_same_content as $exclude_suffix) {

	   if (substr_compare( $field, $exclude_suffix, -strlen( $exclude_suffix ) ) === 0) {
			$field_without_suffix = substr( $field, 0, strlen($field)-strlen($exclude_suffix) );
	   	if ( isset($doc->$field_without_suffix) ) {
				if ( $doc->$field == $doc->$field_without_suffix ) {
			     $exclude_field = TRUE;
		     	}
		   }
	   }

	  }
	  
	  // if not excluded, include field to result
	  if (!$exclude_field) {
	    $fields[] = $field;
	  };
	
	}

	if ($sort) {
		asort($fields);
	}

	return $fields;
}


// print a facet and its values as links
function print_facet(&$results, $facet_field, $facet_label, $facets_limit, $view='list', $pathfacet=FALSE, $path=FALSE) {
	global $cfg, $params, $selected_facets;

	$facetlimit = 50;
	$facetlimit_step = 50;

	if ($pathfacet == 'path') {
		$pathfacet_valueseparator = '/';
	} else {
		$pathfacet_valueseparator = "\t";
	}


	if ($pathfacet) {
		if (isset($facets_limit[$pathfacet])) {
			$facetlimit = $facets_limit[$pathfacet];
		}	
	} else {
		if (isset($facets_limit[$facet_field])) {
			$facetlimit = $facets_limit[$facet_field];
		}
	}

	if (isset($results->facet_counts->facet_fields->$facet_field)) {
		$field = $results->facet_counts->facet_fields->$facet_field;
		$objs = get_object_vars($field);
		$count_facet_values = count($objs);

		# print facet if values in facet
		if ($count_facet_values > 0) {

			?>
<div id="<?= $facet_field ?>" class="facet">
	<h2>
		<?= $facet_label ?>
	</h2>

<?php


// If taxonomy filter, show opened hierarchy before the facet values
if (isset($selected_facets[$pathfacet]) && isset($cfg['facets'][$pathfacet]['tree']) && $cfg['facets'][$pathfacet]['tree']==true) { $is_taxonomy=true;} else { $is_taxonomy=false;}

if ($is_taxonomy==true) {

				$selected_facet = $pathfacet;
				$facetvalue_array = $selected_facets[$selected_facet];

				foreach ($facetvalue_array as $facet_value) {

				      $trimmedpath = trim($facet_value, $pathfacet_valueseparator);

				      $paths = explode($pathfacet_valueseparator, $trimmedpath);

						print '<a onclick="waiting_on();" title="' . t('Remove filter') . '" href="' . buildurl_delvalue($params, $selected_facet, $facet_value, 's', 1) . '">' . $cfg['facets'][$selected_facet]['label'] . '</a><ul>';

				      $fullpath = '';
      				for ($i = 0; $i < count($paths) - 1; $i++) {
      					if ($fullpath != '') {
      						$fullpath .= $pathfacet_valueseparator;
      					}
							$fullpath .= $paths[$i];
							$label = $paths[$i];
							$taxonomy = explode("\t", $label);
							$label = end($taxonomy);

							echo '<ul><li><a onclick="waiting_on();" href="' . buildurl($params, $selected_facet, array($fullpath), 's', 1) . '">' . htmlspecialchars($label) . '</a>' . "\n";
						}
						$label = end($paths);
						$taxonomy = explode("\t", $label);
						$label = end($taxonomy);

						echo '<ul><li><b>' . htmlspecialchars($label) . '</b>';


          }
        }

	
		if ($view=='entities' || $is_taxonomy == true) { ?>
		<ul>
		<?php } else { ?>
		<ul class="no-bullet">
		<?php
		}

		$i = 0;
		foreach ($results->facet_counts->facet_fields->$facet_field as $facet => $count) {

			if ($i<$facetlimit) {

				$label = $facet;

				if ($pathfacet) {

					// if taxonomy (separated by tab), use only last child as label
					$taxonomy = explode("\t", $label);
					$label = end($taxonomy);

					if ($pathfacet_valueseparator == '/') {
					  $link_value = $path . $pathfacet_valueseparator . $facet;
					} else {
					  $link_value = $facet;
					}
					
					$link_filter = buildurl($params, $pathfacet, array($link_value), 's', 1);
					$link_filter_exclude = buildurl_addvalue($params, 'NOT_' . $pathfacet, $link_value, 's', 1);
				
				} else {
					$link_filter = buildurl_addvalue($params, $facet_field, $facet, 's', 1);
					$link_filter_exclude = buildurl_addvalue($params, 'NOT_' . $facet_field, $facet, 's', 1);
				}
				
				if ($view == 'entities') {
					$link_documents = buildurl_addvalue($params, $facet_field, $facet, 'view', null, 's', 1);

	 				print '<li class="entities"><a title="Add to filters" onclick="waiting_on();" href="' . $link_filter . '">'.htmlspecialchars($label).'</a> (<a title="Exclude all results with this entity" onclick="waiting_on();" href="' . $link_filter_exclude . '">-</a>)
in <a title="Search documents for this entity" onclick="waiting_on();" href="' . $link_documents . '">' . $count . ' ' . t('document(s)') . '</a>
					</li>';
					
				} elseif($view == 'graph') {

					$link_documents = buildurl_addvalue($params, $facet_field, $facet, 'view', null, 's', 1);

	 				print '<li class="entities">' . htmlspecialchars($label) . ' in <a title="Search documents for this entity" onclick="waiting_on();" href="' . $link_documents . '">' . $count . ' ' . t('document(s)') . '</a>';

				} else {

		 			print '<li><a onclick="waiting_on();" href="' . $link_filter . '">' . htmlspecialchars($label) . '</a> (' . $count . ')
					<a title="Exclude this value" onclick="waiting_on();" href="' . $link_filter_exclude . '">-</a>
					</li>';
					
				}

			}

			$i++;

		}
		
		if ($is_taxonomy) {
			for ($i = 0; $i < count($paths); $i++) {
      		echo '</li></ul>' . "\n";
		 	}
			echo '</ul>';
		}
		?>
	</ul>
<?php

if ($pathfacet) {
	$facet_field = $pathfacet;
}

$facetlimit_more = $facetlimit + $facetlimit_step;
$facetlimit_less = $facetlimit - $facetlimit_step;
if ($facetlimit_less <= 0) {$facetlimit_less = '0';}

if ($count_facet_values > $facetlimit) {
	$link_facetlimit_more = buildurl($params, $facet='f_'.$facet_field.'_facet_limit', $newvalue=$facetlimit_more);
} else $link_facetlimit_more = '';

if ($facetlimit > 0) {

	$link_facetlimit_less = buildurl($params, $facet='f_'.$facet_field.'_facet_limit', $newvalue=$facetlimit_less);
} else {
	$link_facetlimit_less = '';
}

print '<div><p><small>Show ';

if ($link_facetlimit_less) {
	print '<a href="'.$link_facetlimit_less.'">less (-)</a>';
} else {
	print 'less (-)';
}

print ' | ';

if ($link_facetlimit_more) {
	print '<a href="'.$link_facetlimit_more.'">more (+)</a>';
} else {
	print 'more (+)';
}

print '</small></p></div>';

?>


</div>
<?php
		}
	} // if isset facetfield

}


function strip_empty_lines($s, $max_empty_lines) {

	$result = '';

	$first = true;

	$emptylines = 0;

	$fp = fopen("php://memory", 'r+');
	fputs($fp, $s);
	rewind($fp);
	while( $line = fgets($fp) ) {
		// if only white spaces
		if ( preg_match("/^[\s]*$/", $line) )
		{
				
			$emptylines++;

			// if not max, write empty line to result
			if ($emptylines < $max_empty_lines) {

				// but not if first = beginning of the document
				if ($first == false) {
					$result .= "\n";
				}
			}

		} else { // char is not newline, so reset newline counter and write char to result
			$first = false;
			$emptylines = 0;
				
			$result .= $line;
		}

	}
	fclose($fp);

	return $result;
}


//
// get parameters
//

$query = isset($_REQUEST['q']) ?  trim($_REQUEST['q']) : NULL;

$start = (int) isset($_REQUEST['s']) ? $_REQUEST['s'] : 1;
if ($start < 1) $start = 1;

$sort= isset($_REQUEST['sort']) ? $_REQUEST['sort'] : NULL;

$view = isset($_REQUEST['view']) ? $_REQUEST['view'] : 'list';

if ($view=='words') {
	$cfg['facets']['_text_'] = array('label'=>'Words', 'facet_limit' => 100, 'facet_enabled' => true);
}


$cfg['facets']['location_wkt_ss'] = array('label'=>'Location coordinates', 'facet_limit' => 0, 'facet_enabled' => false);

if ($view=='map') {
	$cfg['facets']['location_wkt_ss']['facet_limit'] = 10000;
}


if ($view == 'rss') {
    $start = 1;
    $sort = 'newest';
}

if ($cfg['etl_status']) {

	$cfg['facets']['etl_error_plugins_ss'] = array ('label'=>'Failed tasks while import & analysis (ETL)');

}

include 'config/config.facets.php';


// get parameters for each configurated facet
$selected_facets = array();
$deselected_facets = array();
$facets_limit = array();

foreach ($cfg['facets'] as $facet=>$facet_value) {

	# facet filter
	if ( isset($_REQUEST[$facet]) ) {
		$selected_facets[$facet] = $_REQUEST[$facet];
	}

	# exclude filter
	if ( isset($_REQUEST['NOT_'.$facet]) ) {
		$deselected_facets[$facet] = $_REQUEST['NOT_'.$facet];
	}

	# facet limit
	if ( isset($_REQUEST['f_'.$facet.'_facet_limit']) ) {
		$facets_limit[$facet] = (int)$_REQUEST['f_'.$facet.'_facet_limit'];
	} elseif(isset($cfg['facets'][$facet]['facet_limit'])) {
		$facets_limit[$facet] = $cfg['facets'][$facet]['facet_limit'];
	}
	
}



// check rights
if (
	($cfg['disable_view_words'] && $view == 'words')
	||
	($cfg['disable_view_graph'] && $view == 'graph')
)
{
	http_response_code(401);
	print ("View not allowed from public internet because could use too many system resources");
	exit;
}



// startdate and enddate
$start_dt = isset($_REQUEST['start_dt']) ? (string)$_REQUEST['start_dt'] : NULL;
$end_dt = isset($_REQUEST['end_dt']) ? (string)$_REQUEST['end_dt'] : NULL;


$zoom = isset($_REQUEST['zoom']) ? (string)$_REQUEST['zoom'] : 'years';


// now we know the view parameter, so lets set limits that fit for the special view
$limit_list = 10;

if ($view=='list') {
	$limit = $limit_list;
}
elseif ($view=='rss') {
	$limit = 20;
}
elseif ($view=='table') {
	$limit = 20;
}
elseif ($view=='images') {
	$limit = 6;
}
elseif ($view=='videos') {
	$limit = 6;
}
elseif ($view=='preview') {
	$limit = 1;
}
elseif ($view=='timeline') {
	$limit = 100;
}
elseif ($view=='graph') {
	$limit = 0;
}
elseif ($view=='trend') {
	$limit = 0;
}
elseif ($view=='map') {
	$limit = 0;
}
elseif ($view=='entities') {
	$limit = 0;
} else $limit=10;


$limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : $limit;
$limit = (int)$limit;

$synonyms = true;
$stemming = true;

if ( isset($_REQUEST["synonyms"]) ) {
	if ( $_REQUEST["synonyms"] == false ) { $synonyms = false; }
} else { $synonyms = false; }

if ( isset($_REQUEST["stemming"]) ) {
	if ( $_REQUEST["stemming"] == false ) { $stemming = false; }
} else { $stemming = false; }


$embedded = NULL;
if ( isset($_REQUEST["embedded"]) ) {
    $embedded = true;
}

$graph_fields=array();
$graph_fl = NULL;

if ( isset($_REQUEST["graph_fl"]) ) {

	$graph_fl = $_REQUEST["graph_fl"];
	$graph_fields = explode(',', $graph_fl);

} else {

	foreach ($cfg['facets'] as $facet => $facet_config) {

		if(isset($facet_config['graph_enabled']) && $facet_config['graph_enabled'] == true) {
				$graph_fields[] = $facet;
		}
	}
}

# if new search, default stemming and synonyms on
if (!$query) {
    $synonyms = true;
    $stemming = true;
}



$operator = 'AND';
if (isset($cfg['operator'])) {

	$operator = $cfg['operator'];

}

$solrfilterquery = "";

if ( isset($_REQUEST['operator']) ) {

	if ($_REQUEST['operator'] == 'AND') {
		$operator = 'AND';
	}
	elseif ($_REQUEST['operator'] == 'OR') {
		$operator = 'OR';
	}
	elseif ($_REQUEST['operator'] == 'Phrase') {
		$operator = 'Phrase';
	}
}

// set all params for urlbuilder which have to be active in the further session
$params = array(
		'q' => $query,
		'sort' => $sort,
		's' => $start,
		'view' => $view,
		'zoom' => $zoom,
		'start_dt' => $start_dt,
		'end_dt' => $end_dt,
		'synonyms' => $synonyms,
		'stemming' => $stemming,
		'operator' => $operator,
		'embedded' => $embedded,
		'graph_fl' => $graph_fl,
);

foreach ($selected_facets as $selected_facet=>$facet_value) {

	$params[$selected_facet] = $facet_value;
}

foreach ($deselected_facets as $deselected_facet=>$facet_value) {

	$params['NOT_'.$deselected_facet] = $facet_value;
}

foreach ($facets_limit as $limited_facet=>$facet_limit) {

	if (isset($cfg['facets'][$limited_facet]['facet_limit']) && $facet_limit != $cfg['facets'][$limited_facet]['facet_limit']) {
		$params['f_'.$limited_facet.'_facet_limit'] = $facet_limit;
	}
}



require_once('./Apache/Solr/Service.php');


$solr = new Apache_Solr_Service($cfg['solr']['host'], $cfg['solr']['port'], $cfg['solr']['path'].'/'.$cfg['solr']['core']);


// get ETL status / count of open ETL tasks
$count_open_etl_tasks_extraction = 0;
$count_open_etl_tasks_ocr = 0;
if ($cfg['etl_status_warning']) {

	$etl_status_solr_query_params['facet'] = "true";
	$etl_status_solr_query_params['facet.query'] = array();
	$etl_status_solr_query_params['facet.query'][] = '{!key=count_open_etl_tasks_extraction}etl_file_b:true AND -etl_enhance_extract_text_tika_server_b:true';
	$etl_status_solr_query_params['facet.query'][] = '{!key=count_open_etl_tasks_ocr}etl_count_images_yet_no_ocr_i:[1 TO *]';

	try {
		$results = $solr->search('*:*', 0, 0, $etl_status_solr_query_params);
		
		$count_open_etl_tasks_extraction = (int)($results->facet_counts->facet_queries->count_open_etl_tasks_extraction);
		$count_open_etl_tasks_ocr = (int)($results->facet_counts->facet_queries->count_open_etl_tasks_ocr);
	} catch (Exception $e) {
		$error = $e->__toString();
	}
}

// if magic quotes is enabled then stripslashes will be needed
if (get_magic_quotes_gpc() == 1) {
	$query = stripslashes($query);
}

// If no query, so show last documents
if (!$query) {
	if ($cfg['newest_on_empty_query']) {
		$solrquery = '*:*';
		if (!$sort) {
			$sort = 'newest';
		}
	}

} else {
	// Build query for Solr

	// mask query for Solr
	$solrquery = mask_query( $query, $cfg['facets'] );

	
	if ($operator == 'Phrase') {
		$solrquery = '"' . $solrquery . '"';
	}

	// fields
	$additionalParameters['qf'] = '_text_^3 title_txt^5';

	if ($stemming == true || $synonyms == true) {

		// add stemmed fields to query fields with boost 2 so same word in other form more relevant than a synonym maybe coming from later fields

		foreach($cfg['languages'] as $language) {
			$additionalParameters['qf'] .= ' text_txt_'.$language.'^2';
		}
	}
	
	if ($synonyms == true) {

		// add fields with synonyms to query fields
		$additionalParameters['qf'] .= ' synonyms^1';
	
		foreach($cfg['languages'] as $language) {
			$additionalParameters['qf'] .= ' synonyms_'.$language.'^1';
		}
	}

	// boost ranking of documents matching whole phrase
	$additionalParameters['pf'] =	$additionalParameters['qf'];

}


/*
* Fields to select
*
* Especially the field "content_txt" maybe too big for php's RAM or causing bad
* for performance, so select only needet fields we want to print except if
* table view (where we want to see all fields)
*/
if ($view != 'table' && $view != 'preview') {
	$additionalParameters['fl']='id,content_type_ss,title_txt,container_s,author_ss,file_modified_dt,last_modified_dt,Content-Length_i,location_p';

	if ($cfg['etl_status']) {
		$additionalParameters['fl']=$additionalParameters['fl'] . ',etl_file_b,etl_enhance_extract_text_tika_server_b';
	}
}


// if listview add (custom) fields to results for printing named entites snippets
if ($view == 'list') {
	foreach ($cfg['facets'] as $facet => $facet_config) {

		if (isset($facet_config['snippets_enabled']) && $facet_config['snippets_enabled'] == true) {
			$additionalParameters['fl'] .= ',' . $facet;
		}
	}
}


// Multi term synonyms
// so set split on whitespace parameter to false
$additionalParameters['sow'] = 'false';


//
// Highlighting
//

if ($view != 'trend' && $view!= 'entities' && $view != 'map' && $view !='graph' && $view!='words') {
	$additionalParameters['hl'] = 'true';
}

$additionalParameters['hl.encoder'] = 'html';
$additionalParameters['hl.snippets'] = 100;
$additionalParameters['hl.fl'] = 'content_txt,title_txt,ocr_t';

$additionalParameters['hl.fragsize'] = $cfg['snippetsize'];

$additionalParameters['hl.simple.pre'] = '<mark>';
$additionalParameters['hl.simple.post'] = '</mark>';


if ($view =="preview") {
	$additionalParameters['hl.fragsize'] = '0';
	$additionalParameters['hl.maxAnalyzedChars'] = '1000000000';
}

elseif ($view =="table") {
	$additionalParameters['hl.fragsize'] = 100;
}

elseif ($view =="words") {

	$additionalParameters['hl'] = 'false';

} elseif ($view != 'table') {
	// if there is no snippet for content field, show first part of content field
	// (i.e. if search matches against filename or all results within only facet filters like path or date)
	$additionalParameters['f.content_txt.hl.alternateField'] = 'content_txt';
	$additionalParameters['f.content_txt.hl.maxAlternateFieldLength'] = $cfg['snippetsize'];
}

// if no query, snippets are generated only by first part of content, so for better performance only this first part has to be analyzed by Solr highligting component
if ($view != 'preview' && !$query) {
	$additionalParameters['hl.maxAnalyzedChars'] = $cfg['snippetsize'];
}

// Solrs default is OR (document contains at least one of the words), we want AND (documents contain all words)
if ($operator == 'OR') {
	$additionalParameters['q.op'] = 'OR';
} else {
	$additionalParameters['q.op'] = 'AND';
}


//
// Facets
//


// build filters and limit parameters from all selected facets and facetvalues
// and extend the Solr query with this filters

foreach ($cfg['facets'] as $configured_facet => $facet_config) {

	if ($configured_facet == 'path') {
		$pathfacet_suffix = '_s';
		$pathfacet_valueseparator = '/';
	} else {
		$pathfacet_suffix = '_ss';
		$pathfacet_valueseparator = "\t";

		$arr_facets[] = $configured_facet . '0' . $pathfacet_suffix;
	}


	if (isset($facet_config['tree']) && $facet_config['tree'] == true) {

		$arr_facets[] = $configured_facet . '0' . $pathfacet_suffix;
		
	} else {
		if ($configured_facet != 'path') {
			$arr_facets[] = $configured_facet;
		}
	}


	if (isset($facet_config['tree']) && $facet_config['tree'] == true) {

		// map virtual taxonomy facet to Solr facet

		$pathfacet = $configured_facet . '0' . $pathfacet_suffix;

		$cfg['facets'][$configured_facet]['pathfacet'] = $pathfacet;
	
		// limit / count of values in path facet
		if (isset($facets_limit[$configured_facet])) {
			$additionalParameters['f.' . $pathfacet . '.facet.limit'] = $facets_limit[$configured_facet] + 1;
		}

	}

	elseif (isset($facets_limit[$configured_facet])) {
		// limit / count of values in facet
		$additionalParameters['f.' . $configured_facet . '.facet.limit'] = $facets_limit[$configured_facet] + 1;

	}


	// add filters for selected facet values to query
	// todo: if flat view (like as graph or entities, the filters have to be hierarchical if taxonomy facet)
	if (isset($selected_facets[$configured_facet])) {

		$selected_facet = $configured_facet;
		foreach ($selected_facets[$selected_facet] as $selected_value) {

			if (isset($facet_config['tree']) && $facet_config['tree'] == true) {
	
				$trimmedpath = trim($selected_value, $pathfacet_valueseparator);
				$paths = explode($pathfacet_valueseparator, $trimmedpath);
	
				// if path check which path_x_s facet to select
				$pathdeepth = count($paths);

				$pathfacet = $configured_facet . $pathdeepth . $pathfacet_suffix;
				
				$arr_facets[] = $pathfacet;
	
				// limit / count of values in facet
				if (isset($facets_limit[$selected_facet])) {
					$additionalParameters['f.' . $pathfacet . '.facet.limit'] = $facets_limit[$selected_facet] + 1;
				}
	
				$cfg['facets'][$configured_facet]['pathfacet'] = $pathfacet;
				
				$cfg['facets'][$configured_facet]['path'] = $selected_value;
				
				if ($pathfacet_valueseparator == '/') {
					$pathfilter = path2query($selected_value, $selected_facet, $pathfacet_suffix, $pathfacet_valueseparator);
					$solrfilterquery .= ' +' . $pathfilter;
				} else {

					$filterpathdeepth = count($paths) - 1;
					
					$filterpathfacet = $configured_facet . $filterpathdeepth . $pathfacet_suffix;
					#mask special chars in facet name
					$solrfacet = addcslashes($filterpathfacet, '+-&|!(){}[]^"~*?:\/ ');
					#mask special chars in facet value
					$solrfacetvalue = addcslashes($selected_value, '+-&|!(){}[]^"~*?:\/ ');
					$solrfacetvalue = str_replace("\t", "\\\t", $solrfacetvalue);
				
					$solrfilterquery .= ' +' . $solrfacet . ':' . $solrfacetvalue;
				
				}
				
				# filter only facet values of the opened path for the case there are additional other values of a multi valued taxonomy at same depth from other hierarchy in the documents that match the selected hierarchy
				if ($configured_facet != 'path') { // do not do that for not multivalued path index structure, where only part of path (without parents) per taxonomy field
					$additionalParameters['f.' . $pathfacet . '.facet.prefix'] = $selected_value . "\t";
				}
			} else {

				#mask special chars in facet name
				$solrfacet = addcslashes($selected_facet, '+-&|!(){}[]^"~*?:\/ ');
				#mask special chars in facet value
				$solrfacetvalue = addcslashes($selected_value, '+-&|!(){}[]^"~*?:\/ ');
				$solrfacetvalue = str_replace("\t", "\\\t", $solrfacetvalue);
				
				$solrfilterquery .= ' +' . $solrfacet . ':' . $solrfacetvalue;
			}
		}
	}

	// add filters for excluded facet values to query
	if (isset($deselected_facets[$configured_facet])) {

		$deselected_facet = $configured_facet;
		foreach ($deselected_facets[$deselected_facet] as $deselected_value) {
	
			if (isset($facet_config['tree']) && $facet_config['tree'] == true) {
				
				$trimmedpath = trim($deselected_value, $pathfacet_valueseparator);
				$paths = explode($pathfacet_valueseparator, $trimmedpath);
	
				// if path check which path_x_s facet to select
				$pathdeepth = count($paths);

				$pathfacet = $configured_facet . $pathdeepth . $pathfacet_suffix;

				$arr_facets[] = $pathfacet;

				if ($pathfacet_valueseparator == '/') {
					$pathfilter = path2query($deselected_value, $deselected_facet, $pathfacet_suffix, $pathfacet_valueseparator);
					$solrfilterquery .= ' -(' . $pathfilter.')';
				} else {

					$filterpathdeepth = count($paths) - 1;
					
					$filterpathfacet = $configured_facet . $filterpathdeepth . $pathfacet_suffix;
					#mask special chars in facet name
					$solrfacet = addcslashes($filterpathfacet, '+-&|!(){}[]^"~*?:\/ ');
					#mask special chars in facet value
					$solrfacetvalue = addcslashes($deselected_value, '+-&|!(){}[]^"~*?:\/ ');
					$solrfacetvalue = str_replace("\t", "\\\t", $solrfacetvalue);
				
					$solrfilterquery .= ' -(' . $solrfacet . ':' . $solrfacetvalue . ')';				
				}


			} else {

				#mask special chars in facet name
				$solrfacet = addcslashes($deselected_facet, '+-&|!(){}[]^"~*?:\/ ');
				#mask special chars in facet value
				$solrfacetvalue = addcslashes($deselected_value, '+-&|!(){}[]^"~*?:\/ ');
				$solrfacetvalue = str_replace("\t", "\\\t", $solrfacetvalue);

				$solrfilterquery .= ' -' . $solrfacet . ':' . $solrfacetvalue;
			}
		}
	}

}

$additionalParameters['facet.field'] = $arr_facets;


function path2query($path, $facet, $pathfacet_suffix, $separator='\t') {
	
	$trimmedpath = trim($path, $separator);
		
	// pathfilter to set in Solr query
	$paths = explode($separator, $trimmedpath);
	
	$pathfilter = '';
	$pathcounter = 0;
	
	$first=true;
	foreach ($paths as $subpath) {
		$solrpath = addcslashes($subpath, '+-&|!(){}[]^"~*?:\/ ');
		$solrpath = str_replace("\t", "\\\t", $solrpath);
		
		if ($first==false) {$pathfilter .= ' +';} else {$first=false;}

		$pathfilter .= $facet . $pathcounter . $pathfacet_suffix . ':' . $solrpath;

		$pathcounter++;
	}
	return $pathfilter;
	
}



// if view is imagegallery extend solrquery to filter images
// filter on content_type image* so that we dont show textdocuments in image gallery
if ($view == 'images') {
	$solrfilterquery .= ' +content_type_ss:image*';
}


// if view is imagegallery extend solrquery to filter images
// filter on content_type image* so that we don't show textdocuments in image gallery
if ($view == 'videos') {
	$solrfilterquery .= ' +(';

	$solrfilterquery .= 'content_type_ss:video*';

	$solrfilterquery .= ' OR content_type_ss:application\/mp4';
	$solrfilterquery .= ' OR content_type_ss:application\/x-matroska';

	$solrfilterquery .= ')';
}


// if view is audio extend solrquery to filter audio files
// filter on content_type audio* so that we don't show textdocuments in image gallery
if ($view == 'audios') {
	$solrfilterquery .= ' +(';

	$solrfilterquery .= 'content_type_ss:audio*';

	$solrfilterquery .= ')';
}


if ($view == 'map') {
	$solrfilterquery .= ' +(';

	$solrfilterquery .= 'location_wkt_ss:*';

	$solrfilterquery .= ')';
}


// which facets to show
$exclude_facets = ['location_wkt_ss'];

// exclude facets, which should be only shown, if open analysis / extraction tasks
if ($count_open_etl_tasks_extraction == 0) {
	$exclude_facets = array_merge($exclude_facets, $cfg['etl_status_facets']);
}

if ( $view == 'entities') {

  // don't show more technical facets in main area, but later as facets in sidebar
  $exclude_entities = ['content_type_ss', 'content_type_group_ss', 'language_s', 'etl_error_plugins_ss'];

  // TODO: add entities_enabled and entities_limit to facet config and facet config ui

  // show only facets, that excluded in entities view main area where all important facets are shown
  foreach ($cfg['facets'] as $facet => $facet_config) {
    if ( !in_array($facet, $exclude_entities) ) {
      $exclude_facets[] = $facet;
    }
  }

}

//
// date filter
//
if ($start_dt || $end_dt) {

	// todo: filter []'" to prevent injections
	if ($start_dt) {
		// dont mask : and - which are used to delimiter date and time values
		$start_dt_solr .= addcslashes($start_dt, '&|!(){}[]^"~*?\/');
	} else $start_dt_solr = '*';

	if ($end_dt) {
		// dont mask : and - which are used to delimiter date and time values
		$end_dt_solr .= addcslashes($end_dt, '&|!(){}[]^"~*?\/');
	} else $end_dt_solr = '*';

	$solrfilterquery .= ' +file_modified_dt:[ ' . $start_dt_solr . ' TO ' .$end_dt_solr. ']';
}


//
// Sort
//

// (Solr default: score)
if ($sort == 'newest') {
	$additionalParameters['sort'] = "file_modified_dt desc";
} elseif ($sort == 'oldest') {
	$additionalParameters['sort'] = "file_modified_dt asc";
} elseif ($sort) {
	$additionalParameters['sort'] = addcslashes($sort, '+-&|!(){}[]^"~*?:\/');
}

// todo: Get similar queries for "Did you mean?"

// facets
$additionalParameters['facet'] = 'true';
$additionalParameters['facet.mincount'] = 1;

// base facets fields
$arr_facets = array('file_modified_dt');


$additionalParameters['f.file_modified_dt.facet.mincount'] = 0;
$additionalParameters['facet.range'] = 'file_modified_dt';

// date facet as ranges
if ($zoom=='years') {
	$gap='+1YEAR';
	$downzoom = 'year';
	$upzoom = false;
	$upzoom_start_dt = false;

} elseif ($zoom=='year') {
	$gap='+1MONTH';
	$date_label = substr($start_dt, 0, 4);
	$downzoom = 'month';
	$upzoom_label = 'Last years';
	$upzoom = 'years';
	$upzoom_start_dt = false;

} elseif ($zoom=='month') {
	$gap='+1DAY';
	$date_label = substr($start_dt, 0, 7);
	$downzoom = 'day';
	$upzoom = 'year';
	$upzoom_label = substr($start_dt, 0, 4);
	$upzoom_start_dt = substr($start_dt, 0, 4) . '-01-01T00:00:00Z';
	$upzoom_end_dt = substr($start_dt, 0, 4) . '-01-01T00:00:00Z+1YEAR';

} elseif ($zoom=='day') {
	$gap='+1HOUR';
	$date_label = substr($start_dt, 0, 10);
	$upzoom = 'month';
	$downzoom = 'hour';
	$upzoom_label = substr($start_dt, 0, 7);
	$upzoom_start_dt = substr($start_dt, 0, 7) . '-01T00:00:00Z';
	$upzoom_end_dt = substr($start_dt, 0, 7) . '-01T00:00:00Z+1MONTH';

} elseif ($zoom=='hour') {
	$gap='+1MINUTE';
	$date_label = substr($start_dt, 0, 13);
	$downzoom = 'minute';
	$upzoom = 'day';
	$upzoom_label = substr($start_dt, 0, 10);
	$upzoom_start_dt = substr($start_dt, 0, 10) . 'T00:00:00Z';
	$upzoom_end_dt = substr($start_dt, 0, 10) . 'T00:00:00Z+1DAY';

} else {
	$gap='+1YEAR';
	$upzoom = 'years';
	$downzoom = 'year';
}


$additionalParameters['facet.range.gap'] = $gap;


// start and end dates
if ($start_dt)	{
	$additionalParameters['facet.range.start'] = (string)$start_dt;
} else {

	if ($zoom='trend') {
		$additionalParameters['facet.range.start'] = '1980-01-01T00:00:00Z/YEAR';
	}else {
		// todo: more then last 10 years if wanted

		$additionalParameters['facet.range.start'] = 'NOW-3YEARS/YEAR';
	}
}

if ($end_dt)	{
	$additionalParameters['facet.range.end'] = (string)$end_dt;
} else {
	$additionalParameters['facet.range.end'] = 'NOW+1YEARS/YEAR';
}


if ($upzoom) {
	$upzoom_link = buildurl($params, 'start_dt', $upzoom_start_dt, 'end_dt', $upzoom_end_dt, 'zoom', $upzoom);
}
else {
	$upzoom_link = false;
}


# use edismax as query parser
$additionalParameters['defType'] = 'edismax';

# do not show indexed segments used for previews / links to matching single pages
$additionalParameters['fq'] = array();
#$additionalParameters['fq'] = array('-content_type_ss:("PDF page")');

# set filter query
if ($solrfilterquery) {
	$additionalParameters['fq'][] = $solrfilterquery;
}

if ($cfg['debug']) {
	print htmlspecialchars($solrquery) . '<br>';
	print_r($additionalParameters);
}

// There is a query, so ask Solr
if ($solrquery or $solrfilterquery) {

	$results = false;
	try {
		$results = $solr->search($solrquery, $start - 1, $limit, $additionalParameters);
		$error = false;
	} catch (Exception $e) {
		$error = $e->__toString();
	}
			

} // isquery -> Ask Solr


if ($cfg['debug']) {
	print "Solr results:";
	print_r($results);
}


//
// Pagination
//

// display results
$total = 0;
if (!empty($results->response)) {
	$total = (int)($results->response->numFound);
}

// calculate stats
$start = min($start, $total);
$end = min($start + $limit - 1, $total);

$stat_limit = $limit;
if ($stat_limit > $total) { $stat_limit=$total; }

if ($limit > 0) {
    $page = ceil($start / $limit);
    $pages = ceil($total / $limit);
}
else {
    $page = 1;
    $pages = 1;
}

// if isnextpage build link
if ($total > $start + $limit - 1) {
	$is_next_page = true;
	$link_next = buildurl($params, 's', $start + $limit);
} else {
	$is_next_page = false;
}

// if isprevpage build link
if ($start > 1) {
	$is_prev_page = true;
	$link_prev = buildurl($params, 's', $start - $limit);

} else {
	$is_prev_page = false;
}


//
// RSS feed
//
$link_rss = buildurl($params, 'view', 'rss', 's', null, 'zoom', null);


//
// General links
//

// link to help
$uri_help = get_uri_help ( $cfg['language'] );


// hidden form parameters if new query / posting form
// to preserve all old params
// - but reset paging (parameter s)
// - remove parameters that are defined by post form (search settings) itself
$form_hidden_parameters = buildform($params, 'q', NULL, 's', NULL, 'operator', NULL, 'synonyms', NULL, 'stemming', NULL);

$datevalues = get_datevalues($results, $params, $downzoom);

if ($view == 'rss') {
	include "templates/view.rss.php";
} elseif ($embedded == true) {
	include "templates/view.embedded.php";
} else {
	include "templates/view.index.php";
}

?>
