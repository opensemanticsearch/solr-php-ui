<?php
// SOLR PHP Client UI
//
// PHP-UI of Open Semantic Search - https://www.opensemanticsearch.org
//
// 2011 - 2017 by Markus Mandalka - https://www.mandalka.name
// and others (see Git history)
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
$cfg['facets']=array(
		'meta_date_dts' => array ('label'=>'Dates'),
		'author_s' => array ('label'=>'Author'),
		'email_ss' => array ('label'=>'Email'),
		'message_from_ss' => array ('label'=>'Message from'),
		'message_to_ss' => array ('label'=>'Message to'),
		'message_cc_ss' => array ('label'=>'Message cc'),
		'message_bcc_ss' => array ('label'=>'Message bcc'),
);


// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');


// include configs
include 'config/config.php';
include 'config/config.facets.php';
include 'config/config.mimetypes.php';
include 'config/config.i18n.php';


# mask special chars but not operators
function mask_query ( $query, $facets=array() ) {

	$unmaskfacets = array('id','title','content','exact','_text_','stemmed');
	
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

	// if param false, delete it
	foreach ($params as $key=>$value) {

		if ($value == false) {
			unset($params[$key]);
		}

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

	// if param false, delete it
	foreach ($params as $key=>$value) {

		if ($value == false) {
			unset($params[$key]);
		}

	}

	$uri = "?" . http_build_query($params);

	return $uri;

}

function buildurl_delvalue($params, $facet=NULL, $delvalue=NULL, $changefacet=NULL, $newvalue=NULL) {

	if ($facet) {

		unset( $params[$facet][array_search($delvalue, $params[$facet])] );

	}


	if ($changefacet) {
		$params[$changefacet] = $newvalue;
	}

	// if param false, delete it
	foreach ($params as $key=>$value) {

		if ($value==false) {
			unset($params[$key]);
		}

	}

	$uri = "?" . http_build_query($params);

	return $uri;

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
function print_facet(&$results, $facet_field, $facet_label) {
	global $params;

	if (isset($results->facet_counts->facet_fields->$facet_field)) {
		if (count(get_object_vars($results->facet_counts->facet_fields->$facet_field)) > 0) {

			?>
<div id="<?= $facet_field ?>" class="facet">
	<h2>
		<?= $facet_label ?>
	</h2>
	<ul class="no-bullet">
		<?php

		foreach ($results->facet_counts->facet_fields->$facet_field as $facet => $count) {
			print '<li><a onclick="waiting_on();" href="' . buildurl_addvalue($params, $facet_field, $facet, 's', 1) . '">' . htmlspecialchars($facet) . '</a> (' . $count . ')
			<a title="Exclude this value" onclick="waiting_on();" href="' . buildurl_addvalue($params, 'NOT_'.$facet_field, $facet, 's', 1) . '">-</a>
			</li>';
		}
		?>
	</ul>
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


// split query by space
// but if phrase "" dont split quoted spaces

function split_but_not_phrase($query) {

	$quoted = false;
	$querypart = '';
	$queryparts = array();
	
	$tokens = explode(" ", $query);
	
	foreach ($tokens as $token) {

		// if querypart before was not closed, add space so we beware the space within quotes even if splited with space
		if ($querypart != '') { $querypart .= ' '; };		
		
		// print "<br>Token: " . $token . "<br>";
	
		$chars = str_split($token);

		// add each char of token to querypart and analyze if it opens a quote
		foreach($chars as $char){
			
			// print $char."<br>";
			
			if ($char == "\"") {
				// todo: if \ before " dont handle as quote because masked quote char
				if ($quoted) {
					$quoted = false;
				} else {
					$quoted = true;
				}
			}

			//print "Adding char ".$char . "<br>";
			$querypart = $querypart.$char;
			//print "Querypart after adding: ".$querypart . "<br>";
				
		} // for each char
		
		// if no quote, add this query part to queryparts, else continue to add tokens and chars until quote ends before adding the ending querypart
		// todo: if last querypart but no end of quoting, add despite of that (so that it is not cutted) and warn that quoting is not closed
		if ($quoted == false) {
			//print "adding splitted querypart:".$querypart."<br>";
			$queryparts[] = $querypart;
			$querypart = '';
		}
		
	} // for each token
	
	return $queryparts;
}

function is_wildcard($query) {
	if ( strpos($query, '*') || strpos($query, '?') ) {
		return true; 
	} else {
	 	return false;
	}
}

// set facet for query parts, but protect operators before this keyword like + - or (
function switch_to_facet($querypart, $facet) {

	// but we must protect one ( or more (( ... if on beginning
	
	$protectprefix = '';

	while ( $querypart[0]=='(' || $querypart[0]=='-' || $querypart[0]=='+' ) {
		$protectprefix .= $querypart[0];
		$querypart = substr($querypart, 1);
	}

	// switch that querypart to the facet _text_ which is not stemmed
	$querypart = $protectprefix . $facet . ":" . $querypart;

	return $querypart;
}


// how often a ( was opened and not closed

function openings($query) {
	
	$chars = str_split($query);
	
	$openings = 0;
	
	// add each char of token to querypart and analyze if it opens a quote
	foreach($chars as $char){
	
		if ($char == "(") {
			$openings += 1;
		}
		
		if ($char == ")") {
			$openings -= 1;
		}
		
	} // for each char
	
	//print "Openings:".$openings."<br>";
	return $openings;
}



// Solr has problems using wildcards in stemmed fields and disables stemming for query parts with wildcards but the index is stemmed, so they are not found in the stemmed index
// So we disable stemming by using unstemmed field for all words with wildcards

// test f.e. with ((exact:"ab xy") OR ("cd xy") AND a AND (b OR c* Or d?e))

function disable_stemming_for_wildcards($query) {

	//print "query pre disable_stemming_for_wildcards:".$query."<br>";
	
	//
	// don't split token if space(s) after "facet:"
	//
	
	// so delete not needed whitespaces after ":"

	// strip whitespaces, if more than one
	$str = preg_replace('/\s\s+/', ' ', $str);

// todo/bug: seems not to work
	// if a (rest) whitespace after : strip it (replace " :" to ":")
	$str = preg_replace('/\:\s/', '\:', $str);

	// split query to queryparts by space, but not if space is part of a phrase quoted by "
	$queryparts = split_but_not_phrase($query);
	
	//
	// build query again but swich facet to _text_ if wildcards but not if another facet yet set for this query part
	//
	
	$query = '';
	$first = true;
	$openings = 0;
	
	foreach ($queryparts as $querypart) {

		//print "Query part:" . $querypart . "<br>";
		
		// add delimiter (space) if not first query part
		if ($first)	{
			$first = false;
		} else {
			$query .= " ";
		}
		
		
		// is a facet defined (i.e. by facetname:facetvalue) ?
		if ( strpos($querypart, ':') == true ) {
			$is_facet_start = true;
		} else {
			$is_facet_start = false;
		}

		
		
		//
		// How deep are we in a facet ?
		// by how many ( were opened and not yet closed after facet definition)
		//		

		// analyse if/how deep querypart inside (previous) facet
		// example: facet: (a* AND (b* OR c))
		
		// by count ( and ) occuring after ":" (starting or within facets (deepnes > 0))
		// for example if (facetA:x AND y AND facetB:z) dont count ( openings

		
		// if a facet definition starts in this query part
		if ($is_facet_start) {
		
			// count/analyse (()) after : 
			// because should not count prefacet openings like the first two in ((facetA:x AND y AND facetB:z))
			$querypart_without_facet_prefix = substr( $querypart, strpos($querypart, ":")+1 );

			$openings += openings($querypart_without_facet_prefix);

			// but dont count closings after facet without opening within a facet before like
			// (a AND title:b)
			if ($openings < 0) {
				$openings = 0;
			}
			
			//print "Withoutfacetprefix:".$querypart_without_facet_prefix."<br>";

		}


		
		// Is a facet defined yet?
		
		// It is if openings (within () of a facet) or "facetname:" defined in this querypart)
		if ($is_facet_start || $openings > 0) {
			$is_within_facet = true;
		} else {
			$is_within_facet = false;
		}
		
		
		// If not facet defined yet by "facetname:" at beginning, we can switch the facet of wildcarded querypart to wildcardfriendly unstemmed facet field _text_
		if ($is_within_facet==false) {
		
			// If wildcard (i.e. * or ?) switch to facet not stemmed:
			if ( is_wildcard($querypart) ) {
				// switch that querypart to the facet _text_ which is not stemmed
				$querypart = switch_to_facet($querypart, "_text_");
			}
		}		


		// count closing ) so we know in next iteration if yet in facet
		if ($is_facet_start==false && $openings > 0) {
				
			$openings += openings($querypart);
		
		}
		
		//print "Openings sum:" . $openings . "<br>";
		
		
		$query .= $querypart;
		
		
	}
	
	//print "<br>Query after disable_stemming_for_wildcards:".$query."</br>";
	return $query;
}






//
// get parameters
//

$query = isset($_REQUEST['q']) ?  trim($_REQUEST['q']) : false;
$start = (int) isset($_REQUEST['s']) ? $_REQUEST['s'] : 1;
if ($start < 1) $start = 1;



$sort= isset($_REQUEST['sort']) ? $_REQUEST['sort'] : false;


$path= isset($_REQUEST['path']) ? $_REQUEST['path'] : false;
$deselected_paths = array();
$deselected_paths = $_REQUEST['NOT_path'];


$types = isset($_REQUEST['type']) ? $_REQUEST['type'] : false;

$not_content_types = array();
$not_content_types = isset($_REQUEST['NOT_content_type']) ? $_REQUEST['NOT_content_type'] : false;


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


	# limit
	if ( isset($_REQUEST['f_'.$facet.'_facet_limit']) ) {
		$facets_limit[$facet] = (int)$_REQUEST['f_'.$facet.'_facet_limit'];

	}
	
}


$view = isset($_REQUEST['view']) ? $_REQUEST['view'] : 'list';

// startdate and enddate
$start_dt = isset($_REQUEST['start_dt']) ? (string)$_REQUEST['start_dt'] : false;
$end_dt = isset($_REQUEST['end_dt']) ? (string)$_REQUEST['end_dt'] : false;


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
elseif ($view=='words') {
	$limit = 150;
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

if (!$query) {
	$synonyms=true;
	$stemming=true;
} else {

 if (isset($_REQUEST["synonyms"])) {
	$synonyms = true;
 } else {
	$synonyms = false;
 }

 if (isset($_REQUEST["stemming"])) {
	$stemming = true;
 } else {
	$stemming = false;
 }

}


$operator = 'OR';

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
		'NOT_content_type' => $not_content_types,
		'sort' => $sort,
		's' => $start,
		'view' => $view,
		'type' => $types,
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

foreach ($facets_limit as $limited_facet=>$facet_value) {

	$params['f_'.$limited_facet.'_facet_limit'] = $facet_value;
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
	

	// Repair wildcard search for wildcarded words if stemming is on
	if ($stemming == true || $synonyms == true) {
	
		if ( is_wildcard($solrquery) ) {
			$solrquery = disable_stemming_for_wildcards($solrquery);
		}
	
	}

	// change default search field, if semantic search
	if ($synonyms == true) {
	
		// stemming + synonyms
		$additionalParameters['df'] = 'synonyms';
	
	} elseif ($stemming == true) {
	
		$additionalParameters['df'] = 'stemmed';
	
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
	$additionalParameters['fl']='id,content_type,title,container_s,author_s,file_modified_dt,last_modified,file_size_i';
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
$additionalParameters['hl.snippets'] = 100;
$additionalParameters['hl.fl'] = 'content';
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
	// if there is no snippet show part of content field
	// (i.e. if search matches against filename or all results within path or date)
	$additionalParameters['hl.alternateField'] = 'content';
	$additionalParameters['hl.maxAlternateFieldLength'] = $cfg['snippetsize'];
}


// Solrs default is OR (document contains at least one of the words), we want AND (documents contain all words)
if ($operator == 'OR') {
	$additionalParameters['q.op'] = 'OR';
} else {
	$additionalParameters['q.op'] = 'AND';
}

// default field to highlight
$highlightfield = 'content';



//
//Facets
//

// build filters and limit parameters from all selected facets and facetvalues
// and extend the solr query with this filters

foreach ($cfg['facets'] as $configured_facet => $facet_config) {

	// limit / count of values in facet
	if (isset($facets_limit[$configured_facet])) {

		$additionalParameters['f.'.$configured_facet.'.facet.limit'] = $facets_limit[$configured_facet];

	}

	// add filters for selected facet values to query
	if (isset($selected_facets[$configured_facet])) {

		$selected_facet = $configured_facet;
		foreach ($selected_facets[$selected_facet] as $selected_value) {

			#mask special chars in facet name
			$solrfacet = addcslashes($selected_facet, '+-&|!(){}[]^"~*?:\/ ');
			#mask special chars in facet value
			$solrfacetvalue = addcslashes($selected_value, '+-&|!(){}[]^"~*?:\/ ');
				
			$solrquery .= ' AND ' . $solrfacet . ':' . $solrfacetvalue;
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
				
			$solrquery .= ' AND NOT ' . $solrfacet . ':' . $solrfacetvalue;
		}
	}

}


// Extend solr query with content_type filter, if set
if ($types) {
	$solrtype = addcslashes($types[0], '+-&|!(){}[]^"~*?:\/ ');
	$solrquery .= ' AND content_type:' . $solrtype. '*';
}

if ($not_content_types) {
	
	foreach ($not_content_types as $not_content_type) {
		$solrtype = addcslashes($not_content_type, '+-&|!(){}[]^"~*?:\/ ');
		$solrquery .= ' AND NOT content_type:' . $solrtype. '*';
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
	
		if ($first==false) {$pathfilter .= ' AND ';} else {$first=false;}
		$pathfilter .='path' . $pathcounter . '_s:' . $solrpath;
		$pathcounter++;
	}
	return $pathfilter;
	
}



if ($deselected_paths) {
	foreach ($deselected_paths as $deselected_path) {
		$pathfilter = path2query($deselected_path);
		$solrquery .= ' AND NOT ('.$pathfilter.')';

	}
}

$pathfacet = 'path0_s';

if ($path) {
	$pathfilter = path2query($path);
	$solrquery .= ' AND '.$pathfilter;
}

// if view is imagegallery extend solrquery to filter images
// filter on content_type image* so that we dont show textdocuments in image gallery
if ($view == 'images') {
	$solrquery .= ' AND content_type:image*';
}


// if view is imagegallery extend solrquery to filter images
// filter on content_type image* so that we don't show textdocuments in image gallery
if ($view == 'videos') {
	$solrquery .= ' AND (';

	$solrquery .= 'content_type:video*';

	$solrquery .= ' OR content_type:application\/mp4';
	$solrquery .= ' OR content_type:application\/x-matroska';

	$solrquery .= ')';
}


// if view is audio extend solrquery to filter audio files
// filter on content_type audio* so that we don't show textdocuments in image gallery
if ($view == 'audios') {
	$solrquery .= ' AND (';

	$solrquery .= 'content_type:audio*';

	$solrquery .= ')';
}


if ($view=='words') {
	$additionalParameters['f._text_.facet.limit'] = $limit;
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

	$solrquery .= ' AND file_modified_dt:[ ' . $start_dt_solr . ' TO ' .$end_dt_solr. ']';
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
		'content_type',
		'file_modified_dt',
		$pathfacet);
// additional facet fields from config
foreach ($cfg['facets'] as $facet => $facet_value) {
	if ($facet != '_text_') {
		$arr_facets[] = $facet;
	}
}


if ($view=='words') {
	// to let solr count the words for a word cloud we want to have the aggregated field text as facet
	$arr_facets[] = '_text_';
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



# Allow wildcards inside phrase search, too
if (strpos($query, "\"") !== false) {
	$additionalParameters['defType'] = 'complexphrase';
} else {
	#but if no phrase use edismax, because complexphrase can not handle date ranges
	$additionalParameters['defType'] = 'edismax';
}

if ($cfg['debug']) {
	print htmlspecialchars($solrquery) . '<br>';
	print_r($additionalParameters);
}

// There is a query, so ask solr
if ($solrquery) {

	$results = false;
	try {
		$results = $solr->search($solrquery, $start - 1, $limit, $additionalParameters);
		$error = false;
	} catch (Exception $e) {

		if ($cfg['debug']) {
			print 'Exception while Solr search. Maybe dirty query. Failover search with edismax.<br>';
		}
			
		//
		// failover with edismax query parser (maybe query is not clean, so query parser "complexphrase" can not handle it)
		//
			
		// humanize query tolerance
		// more forgiving solr query-parser than standard (which would trow erros if user forgot to close ( with ) )
		$additionalParameters['defType'] = 'edismax';

		$results = false;
		try {
			$results = $solr->search($solrquery, $start - 1, $limit, $additionalParameters);
			$error = false;
		} catch (Exception $e) {

			// todo: code temporary not available?
			$error = $e->__toString();
		}
			
	} // failover query with edismax

} // isquery -> Ask solr


if ($cfg['debug']) {

	print "Solr results:";
	print_r($results);
}


//
// Pagination
//

// display results
$total = (int)$results->response->numFound;

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



$datevalues = get_datevalues($results, $params, $downzoom);

if ($embedded) {
	include "templates/view.embedded.php";
} else {
	include "templates/view.index.php";
}

?>
