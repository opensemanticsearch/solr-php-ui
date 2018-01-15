<?php
// SOLR PHP Client UI
//
// PHP-UI of Open Semantic Search - https://opensemanticsearch.org
//
// 2011 - 2017 by Markus Mandalka - https://mandalka.name
// and others (see Git history & issues)
//
// Free Software - License: GPL 3


#
# config defaults
#

# do not change config here, use ./config/config.php!

$cfg['debug'] = false;

$cfg['solr']['host'] = 'localhost';
$cfg['solr']['port'] = 8983;
$cfg['solr']['path'] = '/solr';

$cfg['solr']['core'] = 'core1';

$cfg['languages'] = array('en','es','fr','de','nl','pt','it','cz','ro','ar','fa');

// show newest documents, if no query
$cfg['newest_on_empty_query'] = true;

// no link to admin interface
$cfg['solr']['admin']['uri'] = false;

// only metadata option if server set in config
$cfg['metadata']['server'] = false;

// size of the snippet
$cfg['snippetsize'] = 300;

// todo: convert labels to t() function or read labels from ontology
// and add to facet config: $lang['en']['facetname'] = 'Facet label';
$cfg['facets']=array();


$cfg['disable_view_graph'] = false;
$cfg['disable_view_words'] = false;

// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');


// include configs
include 'config/config.php';
include 'config/config.mimetypes.php';
include 'config/config.i18n.php';


# mask special chars but not operators
function mask_query ( $query, $facets=array() ) {

	$unmaskfacets = array('id','title','content','exact','_text_');
	
	# add configured facets
	foreach ($facets as $facet=>$facetconfig) {
	
		$unmaskfacets[]= $facet ;
		
	}

	# mask special chars for Lucene query, so we can search for that chars, too
	$query = addcslashes( $query, '&|!{}[]^:\/' );

	# todo: mask - if not at beginning
	
	# but unmask, if the char : is used as operator for facet search
	

	foreach ($unmaskfacets as $facet) {

		# undo masking if : is after facet name on beginning of new word or begin of query, because then : is a operator to select a facet for query part
		
		$query = str_replace($facet.'\:', $facet.':', $query);
		
		# maybe todo:
		
		# only if startswith (if startswith - facet, too)
		
		# or after space or (
			
	}

	# map nicer to read facet name "exact" to facet "_text_"
	$query = str_replace('exact:', '_text_:', $query);
	
	
	return $query;
	
}

function get_uri_help ($language) {

	$result = "doc/help." . $language . ".html";

	// if help.$language.html doesn't exist
	if (!file_exists ($result) ) {
		// use default (english)
		$result = "doc/help.html";
	}

	return $result;
}


// create link with actual parameters with one changed parameter
// changing second facet needed if the change of the main parameter will change results to reset page number which could be more than first page

function buildurl($params, $facet=NULL, $newvalue=NULL, $facet2=NULL, $newvalue2=NULL, $facet3=NULL, $newvalue3=NULL, $facet4=NULL, $newvalue4=NULL) {

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

	$uri = "?".http_build_query($params);

	return $uri;

}


function buildurl_addvalue($params, $facet=NULL, $addvalue=NULL, $changefacet=NULL, $newvalue=NULL) {

	if ($facet) {
		$params[$facet][] = $addvalue;
	}

	if ($changefacet) {
		$params[$changefacet] = $newvalue;
	}

	$uri = "?" . http_build_query($params);

	// if param NULL, delete it
	foreach ($params as $key=>$value) {
	    if (is_null($value)) { unset($params[$key]); }
	}

	return $uri;

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

	$uri = "?" . http_build_query($params);


	return $uri;

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



// convert large sizes (in bytes) to better readable unit
function filesize_formatted($size)
{
	$units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	$power = $size > 0 ? floor(log($size, 1024)) : 0;
	return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}




// print a facet and its values as links
function print_facet(&$results, $facet_field, $facet_label, $facets_limit) {
	global $params;

	$facetlimit = 50;
	$facetlimit_step = 50;

	if (isset($facets_limit[$facet_field])) {
		$facetlimit = $facets_limit[$facet_field];
	}

	$count_facet_values = count(get_object_vars($results->facet_counts->facet_fields->$facet_field));

	if (isset($results->facet_counts->facet_fields->$facet_field)) {

		# print facet if values in facet
		if ($count_facet_values > 0) {

			?>
<div id="<?= $facet_field ?>" class="facet">
	<h2>
		<?= $facet_label ?>
	</h2>
	<ul class="no-bullet">
		<?php

		$i = 0;
		foreach ($results->facet_counts->facet_fields->$facet_field as $facet => $count) {

			if ($i<$facetlimit) {
 print '<li><a onclick="waiting_on();" href="' . buildurl_addvalue($params, $facet_field, $facet, 's', 1) . '">' . htmlspecialchars($facet) . '</a> (' . $count . ')
			<a title="Exclude this value" onclick="waiting_on();" href="' . buildurl_addvalue($params, 'NOT_'.$facet_field, $facet, 's', 1) . '">-</a>
			</li>';
			}

			$i++;

		}
		?>
	</ul>
<?php

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

$path= isset($_REQUEST['path']) ? $_REQUEST['path'] : NULL;
$deselected_paths = array();
$deselected_paths = isset($_REQUEST['NOT_path']) ? $_REQUEST['NOT_path'] : '';


$view = isset($_REQUEST['view']) ? $_REQUEST['view'] : 'list';
if ($view=='words') {
	$cfg['facets']['_text_'] = array('label'=>'Words', 'facet_limit' => 100, 'facet_enabled' => true);
}

include 'config/config.facets.php';

// get parameters for each configurated facet
$selected_facets = array();
$deselected_facets = array();
$facets_limit = array();
$not_content_types = array();
$types = array();
$typegroups = array();

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
	} else {
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
if ($view=='list') {
	$limit = 10;
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
} else $limit=10;


$limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : $limit;
if ($limit == "all") {
	$limit = 10000000;
}

$synonyms = true;
$stemming = true;

if ( isset($_REQUEST["synonyms"]) ) {
	if ( $_REQUEST["synonyms"] == false ) { $synonyms = false; }
} else { $synonyms = false; }

if ( isset($_REQUEST["stemming"]) ) {
	if ( $_REQUEST["stemming"] == false ) { $stemming = false; }
} else { $stemming = false; }


# if new search, default stemming and synonyms on
if (!$query) {
    $synonyms = true;
    $stemming = true;
}



$operator = 'OR';
$solrfilterquery = "";

if ( isset($_REQUEST['operator']) ) {

	if ($_REQUEST['operator'] == 'AND') {
		$operator = 'AND';
	}
	if ($_REQUEST['operator'] == 'Phrase') {
		$operator = 'Phrase';
	}
}

// set all params for urlbuilder which have to be active in the further session
$params = array(
		'q' => $query,
		'path' => $path,
		'NOT_path' => $deselected_paths,
		'sort' => $sort,
		's' => $start,
		'view' => $view,
		'zoom' => $zoom,
		'start_dt' => $start_dt,
		'end_dt' => $end_dt,
		'synonyms' => $synonyms,
		'stemming' => $stemming,
		'operator' => $operator,
);

foreach ($selected_facets as $selected_facet=>$facet_value) {

	$params[$selected_facet] = $facet_value;
}

foreach ($deselected_facets as $deselected_facet=>$facet_value) {

	$params['NOT_'.$deselected_facet] = $facet_value;
}

foreach ($facets_limit as $limited_facet=>$facet_limit) {

	if ($facet_limit != $cfg['facets'][$limited_facet]['facet_limit']) {
		$params['f_'.$limited_facet.'_facet_limit'] = $facet_limit;
	}
}



require_once('./Apache/Solr/Service.php');


$solr = new Apache_Solr_Service($cfg['solr']['host'], $cfg['solr']['port'], $cfg['solr']['path'].'/'.$cfg['solr']['core']);

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
	$additionalParameters['qf'] = '_text_';

	if ($stemming == true || $synonyms == true) {

		// boost relevance of exact text field by 20
		$additionalParameters['qf'] .= '^20';

		// add stemmed fields to query fields with bust 5 so same word in other form more relevant than a synonym maybe coming from later fields

		foreach($cfg['languages'] as $language) {
			$additionalParameters['qf'] .= ' text_txt_'.$language.'^5';
		}
	}
	
	if ($synonyms == true) {

		// add synonym enriched fields to query fields
	
		foreach($cfg['languages'] as $language) {
			$additionalParameters['qf'] .= ' synonyms_'.$language.'^1';
		}
	}

}


/*
* Fields to select
*
* Especially the field "content" maybe too big for php's RAM or causing bad
* for performance, so select only needet fields we want to print except if
* table view (where we want to see all fields)
*/
if ($view != 'table' && $view != 'preview') {
	$additionalParameters['fl']='id,title,container_s,author_s,file_modified_dt,last_modified,file_size_i';
}


// if listview add (custom) facet fields to query for printing extracted named entites
if ($view == 'list') {
	foreach ($cfg['facets'] as $facet => $facet_config) {
			
		$additionalParameters['fl'] .= ','.$facet;
	}
}


// Multi term synonyms
// so set split on whitespace parameter to false
$additionalParameters['sow'] = 'false';


//
// Highlighting
//
$additionalParameters['hl'] = 'true';

$additionalParameters['hl.encoder'] = 'html';
$additionalParameters['hl.snippets'] = 100;
$additionalParameters['hl.fl'] = 'content';
foreach ($cfg['languages'] as $language) {
	$additionalParameters['hl.fl'] .= ',content_txt_'.$language;
}

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

} else {
	// if there is no snippet for content field, show part of content field
	// (i.e. if search matches against filename or all results within path or date)
	$additionalParameters['f.content.hl.alternateField'] = 'content';
	$additionalParameters['f.content.hl.maxAlternateFieldLength'] = $cfg['snippetsize'];
}


// Solrs default is OR (document contains at least one of the words), we want AND (documents contain all words)
if ($operator == 'OR') {
	$additionalParameters['q.op'] = 'OR';
} else {
	$additionalParameters['q.op'] = 'AND';
}


//
//Facets
//

// build filters and limit parameters from all selected facets and facetvalues
// and extend the solr query with this filters

foreach ($cfg['facets'] as $configured_facet => $facet_config) {

	// limit / count of values in facet
	if (isset($facets_limit[$configured_facet])) {

		$additionalParameters['f.'.$configured_facet.'.facet.limit'] = $facets_limit[$configured_facet] + 1;

	}

	// add filters for selected facet values to query
	if (isset($selected_facets[$configured_facet])) {

		$selected_facet = $configured_facet;
		foreach ($selected_facets[$selected_facet] as $selected_value) {

			#mask special chars in facet name
			$solrfacet = addcslashes($selected_facet, '+-&|!(){}[]^"~*?:\/ ');
			#mask special chars in facet value
			$solrfacetvalue = addcslashes($selected_value, '+-&|!(){}[]^"~*?:\/ ');
				
			$solrfilterquery .= ' +' . $solrfacet . ':' . $solrfacetvalue;
		}
	}

	// add filters for excluded facet values to query
	if (isset($deselected_facets[$configured_facet])) {

		$deselected_facet = $configured_facet;
		foreach ($deselected_facets[$deselected_facet] as $deselected_value) {

			#mask special chars in facet name
			$solrfacet = addcslashes($deselected_facet, '+-&|!(){}[]^"~*?:\/ ');
			#mask special chars in facet value
			$solrfacetvalue = addcslashes($deselected_value, '+-&|!(){}[]^"~*?:\/ ');
				
			$solrfilterquery .= ' -' . $solrfacet . ':' . $solrfacetvalue;
		}
	}

}


function path2query($path) {
	global $pathfacet;
	
	$trimmedpath = trim($path, '/');
	$paths = explode('/', $trimmedpath);
	
	// if path check which path_x_s facet to select
	$pathdeepth = count($paths);
	
	$pathfacet = 'path' . $pathdeepth . '_s';
	
	// pathfilter to set in Solr query
	$paths = explode('/', $trimmedpath);
	
	$pathfilter = '';
	$pathcounter = 0;
	
	$first=true;
	foreach ($paths as $subpath) {
		$solrpath = addcslashes($subpath, '+-&|!(){}[]^"~*?:\/ ');
	
		if ($first==false) {$pathfilter .= ' +';} else {$first=false;}
		$pathfilter .='path' . $pathcounter . '_s:' . $solrpath;
		$pathcounter++;
	}
	return $pathfilter;
	
}


if ($deselected_paths) {
	foreach ($deselected_paths as $deselected_path) {
		$pathfilter = path2query($deselected_path);
		$solrfilterquery .= ' -('.$pathfilter.')';

	}
}

$pathfacet = 'path0_s';

if ($path) {
	$pathfilter = path2query($path);
	$solrfilterquery .= ' +'.$pathfilter;
}


// if view is imagegallery extend solrquery to filter images
// filter on content_type image* so that we dont show textdocuments in image gallery
if ($view == 'images') {
	$solrfilterquery .= ' +content_type:image*';
}


// if view is imagegallery extend solrquery to filter images
// filter on content_type image* so that we don't show textdocuments in image gallery
if ($view == 'videos') {
	$solrfilterquery .= ' +(';

	$solrfilterquery .= 'content_type:video*';

	$solrfilterquery .= ' OR content_type:application\/mp4';
	$solrfilterquery .= ' OR content_type:application\/x-matroska';

	$solrfilterquery .= ')';
}


// if view is audio extend solrquery to filter audio files
// filter on content_type audio* so that we don't show textdocuments in image gallery
if ($view == 'audios') {
	$solrfilterquery .= ' +(';

	$solrfilterquery .= 'content_type:audio*';

	$solrfilterquery .= ')';
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
$arr_facets = array(
		'file_modified_dt',
		$pathfacet);

// additional facet fields from config
foreach ($cfg['facets'] as $facet => $facet_value) {
	$arr_facets[] = $facet;
}

$additionalParameters['facet.field'] = $arr_facets;

$additionalParameters['f.file_modified_dt.facet.mincount'] = 0;
$additionalParameters['facet.range']= 'file_modified_dt';

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

# set filter query
if ($solrfilterquery) {
	$additionalParameters['fq'] = $solrfilterquery;	
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

$page = ceil($start / $limit);
$pages = ceil($total / $limit);

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

if (isset($embedded) && $embedded) {
	include "templates/view.embedded.php";
} else {
	include "templates/view.index.php";
}

?>
