<?php

require_once(__DIR__ . '/helpers.php');

// Show preview


// print value(s) from field
function print_field($doc, $field, $preview_link='?view=preview&q=id:') {

  $field_linkeddata = $field . '_preflabel_and_uri_ss';

  $linked_values = array();

  // if there, print links to linked data entities
  if (isset($doc->$field_linkeddata)) {

		// if only one value in field, convert to array, so we can handle it with same code
		if (is_array($doc->$field_linkeddata)) {
			$linked_data = $doc->$field_linkeddata;
		} else {
			$linked_data = array($doc->$field_linkeddata);
		}
		print '<ul class="entity">';
      foreach ($linked_data as $value) {
			$label_and_uri = get_preflabel_and_uri($value);
			$label = $label_and_uri['label'];
			$value_uri = $label_and_uri['uri'];
									
			if ($value_uri) {

				// link uri to preview of this id
				$value_uri = $preview_link . urlencode($value_uri);

        		print '<li class="entity"><a target="_blank" href="'. $value_uri . '">' . htmlspecialchars($label) . '</a></li>';
        		$linked_values[] = $label;
			} else {
        		print '<li>' . htmlspecialchars($value) . '</li>';
	     		$linked_values[] = $value;
			}
     	}
    	print '</ul>';
  } else {
						  
	if (is_array($doc->$field)) {
		print "<ul>";
		foreach ($doc->$field as $value) {
			print '<li>' . htmlspecialchars($value) . '</li>';
		}
		print "</ul>";
	} else {
		print htmlspecialchars($doc->$field);
	}


  }

	return $linked_values;

}


foreach ($results->response->docs as $doc) {
	
$id = $doc->id;

$container = isset($doc->container_s) ? $doc->container_s : NULL;
list ($url_display, $url_display_basename, $url_preview, $url_openfile, $url_annotation, $url_container_display, $url_container_display_basenname) = get_urls($doc->id, $container);

// Type
$type = $doc->content_type_ss;

// If multiple types (because recursive content extraction like archives or PDF with embedded images), use the type of the file, only
if (is_array($type)) {
	$type = $type[0];
}

if (isset($doc->content_type_group_ss)) {
    $type_group = $doc->content_type_group_ss;
} else $type_group = false;


// Authors

if (is_array($doc->author_ss)) {
	$authors = $doc->author_ss;
} else {
	$authors = array($doc->author_ss);
}

// Title
$title = format_title($doc->title_txt, t("No Title"));

// Modified date
if (isset($doc->file_modified_dt)) {
  $datetime = $doc->file_modified_dt;
}
elseif (isset($doc->last_modified)) {
  $datetime = $doc->last_modified;
}
else {
  $datetime = FALSE;
}

// File size
$file_size = 0;
$file_size_txt = '';
if (isset($doc->file_size_i)) {
  $file_size = $doc->file_size_i;
  $file_size_txt = filesize_formatted($file_size);
}

$preview_image = FALSE;

$preview_allowed = TRUE;

# if not allowed in config
if (isset($cfg['preview_allowed'])) {
  if (!$cfg['preview_allowed']) {
    $preview_allowed = FALSE;
  }
}

# but ok, if allowed in this document
if (isset($doc->preview_allowed_b)) {
  if ($doc->preview_allowed_b == TRUE) {
    $preview_allowed = TRUE;
  }
  else {
    $preview_allowed = FALSE;
  }
}

if ($preview_allowed) {
  //Content
  $highlightingfield = 'content_txt';
  if (isset($results->highlighting->$id->$highlightingfield)) {
      $content = $results->highlighting->$id->$highlightingfield[0];
  } else {
  	$content = $doc->content_txt;
  	$content = htmlspecialchars($content);
  }

  $content = strip_empty_lines($content, 3);
  $content = nl2br($content); // new lines


  // get all words from query and text matches from plain text / highlightings for PDF preview

  $highlightings = array();
  
  $queryparts = explode(' ',$query);

  foreach ($queryparts as $querypart) {
  	 if ($querypart != "") {
    	if ( !in_array($querypart, $highlightings) ) {
			$highlightings[] = $querypart;
	   }
    }
  }

  preg_match_all("/<mark>([\w\W]*?)<\/mark>/", $content, $matches);

  foreach ($matches[1] as $match) {
    if ( !in_array($match, $highlightings) ) {
		$highlightings[] = $match;    
    }
  }

  $highlightings = implode(" ", $highlightings);

  if (isset($cfg['preview_path']) and isset($doc->preview_s)) {
    $preview_image = $cfg['preview_path'] . $doc->preview_s;
  }

  // if single page pdf segments, search matching pages to start preview with a matching page
  $preview_segments = false;
  $preview_page = 1;
  $preview_pages = 1;
  
  if (isset($doc->etl_thumbnails_s)) {
	  $preview_segments = true;

	  // how many pages?
	    
	  if (isset($doc->pages_i)) {
			$preview_pages = intval($doc->pages_i);
	  } elseif (isset($doc->xmpTPg_NPages_ss)) {
			$preview_pages = intval($doc->xmpTPg_NPages_ss);
	  }

	  # which pages are matching the search query?
  
	  $preview_matching_pages_additionalParameters = array();
	  $preview_matching_pages_additionalParameters['fl'] = 'page_i';
	  $preview_matching_pages_additionalParameters['sort'] = 'page_i asc';
	  $preview_matching_pages_additionalParameters['q.op'] = 'OR';
	  $preview_matching_pages_additionalParameters['fq'] = array('content_type_ss:"PDF page"', 'container_s:"'.mask_query($id).'"');
	  $preview_matching_pages_additionalParameters['qf'] = $additionalParameters['qf'];

	  try {
		$preview_matching_pages_results = $solr->search($solrquery, 0, 10000, $preview_matching_pages_additionalParameters);
		if (!empty($preview_matching_pages_results->response)) {
			$preview_matching_pages_total = (int)($preview_matching_pages_results->response->numFound);

			# set first preview page to first matching page
			if ($preview_matching_pages_total > 0) {
				$preview_page = $preview_matching_pages_results->response->docs[0]->page_i;
	
			}

		}
		$error = false;

	  } catch (Exception $e) {
			$error = $e->__toString();
	  }

  }

	

}
else {
  $content = t('preview_not_allowed');
}


//OCR
$ocr = FALSE;
if (isset($doc->ocr_t) && $preview_allowed) {
  $ocr = strip_empty_lines($doc->ocr_t, 3);
  $ocr = htmlspecialchars($ocr);
  $ocr = nl2br($ocr); // new lines
  if ($ocr == "") {
    $ocr = FALSE;
  }
}

// Show annotation tab ?
$annotations = FALSE;
if ( isset($doc->annotation_text_txt) || isset($doc->annotation_tag_ss) || isset($doc->comment_txt) || isset($doc->tag_ss) ) {
	$annotations = TRUE;
}

// set true until autotagging will activate if values in autotag facets
$annotations = TRUE;

// exclude fields from (meta) data tab which handled by other tabs
$exclude_fields = array('_version_', '_text_', 'title_txt', 'content_txt', 'preview_s', 'ocr_t','X-Parsed-By_ss');
$exclude_fields_prefixes = array('etl_', 'X-TIKA_');
$exclude_fields_suffixes = array('_uri_ss', '_preflabel_and_uri_ss', '_matchtext_ss');

// exclude fields that are only copied for language specific analysis in index
$exclude_fields_suffixes_if_same_content = array();
foreach ($cfg['languages'] as $language) {

	$exclude_fields[] = 'text_txt_' . $language;

	$exclude_fields_suffixes_if_same_content[] = '_txt_' . $language;

}

foreach ($cfg['facets'] as $facet => $facet_config) {
	$exclude_fields[] = $facet;
	$exclude_fields_prefixes[] = $facet . '_taxonomy_';
}
  

$fields = get_fields($doc, $exclude_fields, $exclude_fields_prefixes, $exclude_fields_suffixes, $exclude_fields_suffixes_if_same_content);

?>
<div id="results" class="row">

  <div class="date row">
    <?= $datetime ?>
  </div>

  <div class="row">

    <?php
      include 'templates/view.url.php';
    ?>

		
    <?php if ($file_size_txt) { ?>
      <span class="size">(<?= $file_size_txt ?>)</span>
    <?php } // if filesize?>
  </div>


  <?php if ($authors) {
    print '<div class="author row">' . htmlspecialchars(implode(", ", $authors)) . ':</div>';
  } ?>


  <div class="row">
    <h1><a class="title" target="_blank" href="<?= $url_openfile ?>"><?= $title ?></a>
    </h1>
  </div>

  <hr/>

  <div class="row">


    <ul class="tabs" data-tabs id="preview-tabs">

        <li class="tabs-title is-active"><a href="#preview-content"
                                            aria-selected="true"><?= t('Content') ?></a></li>

      <?php if ($type=='application/pdf') { // if pdf ?>
        <li class="tabs-title"><a href="#preview-plaintext"><?= t('Plain text') ?></a></li>
      <?php } // if pdf ?>


      <?php if ($ocr) { ?>
        <li class="tabs-title"><a href="#preview-ocr">OCR</a></li>
      <?php } // if ocr ?>

      <?php if ($annotations) { ?>
        <li class="tabs-title"><a href="#preview-annotations"><?= t('Tags & Annotations') ?></a></li>
      <?php } // if annotations ?>
 
      <li class="tabs-title"><a href="#preview-meta"><?= t('Meta') ?></a></li>

      <?php if ($cfg['etl_status']) { ?>
        <li class="tabs-title"><a href="#preview-etl"><?= t('Import & analysis process (ETL)') ?></a></li>
      <?php } // if ETL status ?>


    </ul>
  </div>

  <div class="tabs-content" data-tabs-content="preview-tabs">
      <div class="tabs-panel is-active" id="preview-content">

        <div id="content">


          <?php

          if ($preview_allowed) {

            // if PDF
            if (strpos($type, 'application/pdf') === 0) { 


if ($preview_segments == true) {

           ?>


<script type="text/javascript">

	var preview_pages = <?= $preview_pages ?>;
	var preview_page = <?= $preview_page ?>;

	var highlightings_urlencoded = <?= json_encode(rawurlencode($highlightings)) ?>;
	var thumbnails_dir = '<?= 'thumbnails/' . $doc->etl_thumbnails_s ?>';
	
	function show_page(preview_page) {

		var preview_page_url = thumbnails_dir + '/' + preview_page + '.pdf#search=' + highlightings_urlencoded;
		document.getElementById('pdf').setAttribute('src', preview_page_url);

		document.getElementById("preview_page").innerHTML = preview_page + '/' + preview_pages;
		
		if (preview_page == 1) {
			document.getElementById("preview_prev_page").innerHTML = '<?= t(preview_prev_page) ?>';
			document.getElementById("preview_prev_page").classList.add("disabled");
		} else {
			document.getElementById("preview_prev_page").innerHTML = '<a onclick="preview_show_prev_page();"><?= t(preview_prev_page) ?></a>';
			document.getElementById("preview_prev_page").classList.remove("disabled");		
		}

		if (preview_page == preview_pages) {
			document.getElementById("preview_next_page").innerHTML = '<?= t(preview_next_page) ?>';
			document.getElementById("preview_next_page").classList.add("disabled");
		} else {
			document.getElementById("preview_next_page").innerHTML = '<a onclick="preview_show_next_page();"><?= t(preview_next_page) ?></a>';
			document.getElementById("preview_next_page").classList.remove("disabled");		
		}
		
				
	}

	function preview_show_next_page() {
		if (preview_page < preview_pages) {
			preview_page += 1;
			show_page(preview_page);
		}
	}

	function preview_show_prev_page() {
		if (preview_page > 1) {
			preview_page -= 1;
			show_page(preview_page);
		}
	}


</script>

<div class="row">
<ul class="pagination text-center" role="navigation" aria-label="Pagination">


<li id="preview_prev_page" class="pagination-previous <?php if ($preview_page == 1): print 'disabled'; endif; ?>">
<?php if ($preview_page > 1): print '<a onclick="preview_show_prev_page();">'; endif; ?><?= t(preview_prev_page) ?><?php if ($preview_page > 1): print '</a>'; endif; ?>

</li>

<li id="preview_page"><?= $preview_page ?>/<?= $preview_pages ?></li>

<li id="preview_next_page" class="pagination-next <?php if ($preview_page == $preview_pages): print 'disabled'; endif; ?>">
<?php if ($preview_page < $preview_pages): print '<a onclick="preview_show_next_page();">'; endif; ?><?= t(preview_next_page) ?><?php if ($preview_page < $preview_pages): print '</a>'; endif; ?>
</li>

</ul>
</div>
					<embed id="pdf" src="<?= 'thumbnails/' . $doc->etl_thumbnails_s . '/' . $preview_page . '.pdf#search=' . rawurlencode($highlightings) ?>" type="application/pdf" width="100%" height="100%" />


					<?php
					} else { // no thumbnails
					?>
						<embed id="pdf" src="<?= $url_openfile ?>#search=<?= rawurlencode($highlightings) ?>" type="application/pdf" width="100%" height="100%" />

              	<?php
              } // no thumbnails
            } // if PDF


            // if image
            if (strpos($type, 'image') === 0) { ?>
              <a href="<?= $url_openfile ?>" target="_blank"><img src="<?= $url_openfile ?>"/></a>
              <?php
            } // if image

            // if video
            if (strpos($type, 'video') === 0) { ?>
              <video controls="controls" src="<?= $url_openfile ?>"></video>
              <?php
            } // if video

            // if audio
            if (strpos($type, 'audio') === 0) { ?>
              <audio controls="controls" src="<?= $url_openfile ?>"></audio>
              <?php
            } // if audio ?>


            <?php if ( $type == 'CSV row' || strpos($type_group, 'Knowledge graph') === 0 || strpos($type, 'Knowledge graph') === 0 ) {

				  print '<div class="graph">';

              foreach ($fields as $field) {
                if ($field != 'id' and $field != 'content_type_ss' and $field != 'content_type_group_ss' and $field != 'container_s' and isset($doc->$field)) {

						  print '<div>';
                    print "<h2 title=\"" . htmlentities($field) . "\">" . htmlentities(get_label($field)) . '</h2>';


						  $linked_values = print_field($doc, $field);

                    if (is_array($doc->$field)) {
                    		$field_values = $doc->$field;
                    } else {
                    		$field_values = array($doc->$field);
						  }

						  // delete all former printed linked values
						  $field_values = array_diff($field_values, $linked_values);
						  if (!empty($field_values)) {
					
						  	if (!empty($linked_values) ) {
						  		print '<div><small><i>Alternate labels for this entities</i></small>:</div>';
						  	}
						   print ('<ul class="alternatelabel">');
                    	foreach ($field_values as $value) {
                     		print '<li class="alternatelabel">' . htmlspecialchars($value) . '</li>';
                    	}
                     print '</ul>';
						  }
	      			  print '</div>';
                }

              }
            ?>
            </div>

            <?php

            } // if knowledge graph or csv row

				// not pdf, since pdf text previous shown by pdf viewer
				if ($type != 'application/pdf') {

            ?>
				
            <?= $content ?>

            <?php
            
            } // not PDF
            
            if ($ocr) {
              print "<b>" . t("content_ocr") . "</b><br/>";
              print $ocr;
            }

          } // preview allowed
          else {
            print (t('preview_not_allowed'));
          }
          ?>


        </div>
      </div>


      <?php if ($type=='application/pdf' && $preview_allowed) { // if pdf ?>
        <div class="tabs-panel" id="preview-plaintext">

          <div id="plaintext">
            <?= $content ?>
          </div>
        </div>
      <?php } // if pdf ?>


      <?php if ($ocr && $preview_allowed) { ?>
        <div class="tabs-panel" id="preview-ocr">

          <div id="ocr"><?php
            print "<i>" . t("content_ocr") . "</i><br/>";
            ?>
            <?= $ocr ?>
          </div>
        </div>
      <?php } // if ocr ?>

      <?php if ($annotations && $preview_allowed) { ?>
        <div class="tabs-panel" id="preview-annotations">

			<?php
			if (isset($doc->annotation_tag_ss)) {
				print '<h3>Tags (Hypothesis)</h3>';
				print_field($doc, 'annotation_tag_ss');
			}

			if (isset($doc->comment_txt)) {
				print '<h3>Notes</h3>';
				print_field($doc, 'comment_txt');
			}
			if (isset($doc->annotation_text_txt)) {
				print '<h3>Annotations (Hypothesis)</h3>';
				print_field($doc, 'annotation_text_txt');
			}


  foreach ($cfg['facets'] as $facet => $facet_config) {
	if (isset($doc->$facet) && $facet!='author_ss' && $facet!='language_s' && $facet!='content_type_ss' && $facet!='content_type_group_ss') {
    	print ( '<h4>' . t($facet_config['label']) . '</h4>' );
    	// since taged entities of thesaurus instead imported knowledgegraph, link to entities index preview
      print_field($doc, $facet, '/search-apps/search_entity/index?doc=' . rawurlencode($doc->id) .'&id=');
	}
  }


			?>

        </div>
      <?php } // if annotations ?>


      <div class="tabs-panel" id="preview-meta">
        <div class="meta">
          <?php
          foreach ($fields as $field) {
            if (isset($doc->$field)) {
              ?>
              <div>
                <?php
                print "<b><span title=\"" . htmlentities($field) . "\">" . htmlentities(get_label($field)) . '</span></b>:<br />';

					 print_field($doc, $field);

                print '<br /><br />';
                ?>
              </div>
              <?php
            }
          }
          ?>
        </div>

      </div>

      <?php if ($cfg['etl_status']) { ?>


      <div class="tabs-panel" id="preview-etl">
        <div class="etl">
          <?php
			 foreach ($doc as $field => $value) {
            if (strpos($field, 'etl_') === 0 or strpos($field, 'X-TIKA_') === 0 or $field=='X-Parsed-By_ss') {
              ?>
              <div>
                <?php
                print "<b><span title=\"" . htmlentities($field) . "\">" . htmlentities(get_label($field)) . '</span></b>:<br />';

					 print_field($doc, $field);

                print '<br /><br />';
                ?>
              </div>
              <?php
            }
          }
          ?>
        </div>

      </div>

      <?php } // if ETL status ?>



    </div>

    <hr/>

    <?php
      include 'templates/view.commands.php';
    ?>

    <?php
    } // foreach doc
    ?>

  </div>
