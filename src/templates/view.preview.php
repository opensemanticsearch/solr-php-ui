<?php

// Show preview


foreach ($results->response->docs as $doc) {
	
$id = $doc->id;

// Type
$type = $doc->content_type;


// URI

// if part of container like zip, link to container file
// if PDF page URI to Deeplink
// since PDF Reader can open deep links
if (isset($doc->container_s) and $type != 'PDF page') {
  $uri = $doc->container_s;
  $deepid = $id;

}
else {
  $uri = $id;
  $deepid = FALSE;
}


$uri_label = $uri;
$uri_tip = FALSE;

// if file:// then only filename
if (strpos($uri, "file://") == 0) {
  $uri_label = basename($uri);
  // for tooptip remove file:// from beginning
  $uri_tip = substr($uri, 7);
}

if ($deepid) {
  $deep_uri_label = $deepid;
  $deep_uri_tip = FALSE;
  // if file:// then only filename
  if (strpos($deepid, "file://") == 0) {
    $deep_uri_label = basename($deepid);
    // for tooptip remove file:// from beginning
    $deep_uri_tip = substr($deepid, 7);
  }
}


// Author
$author = htmlspecialchars($doc->author_s);
// Title
$title = t("No Title");
if (isset($doc->title)) {
  if (!empty($doc->title)) {
    $title = htmlspecialchars($doc->title);
  }
}

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

  $content = $doc->content;
  $content = htmlspecialchars($content);

  // if highligting available for the language, use highlighted content
  foreach ($cfg['languages'] as $language) {
    $language_specific_fieldname = 'content_txt_' . $language;
    if (isset($results->highlighting->$id->$language_specific_fieldname)) {
      $content = $results->highlighting->$id->$language_specific_fieldname[0];
    }
  }

  $content = strip_empty_lines($content, 3);
  $content = nl2br($content); // new lines


  if (isset($cfg['preview_path']) and isset($doc->preview_s)) {
    $preview_image = $cfg['preview_path'] . $doc->preview_s;
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

$exclude_fields = array('_version_', 'title', 'content', 'preview_s', 'ocr_t');

// exclude fields that are only copied for language specific analysis in index
foreach ($cfg['languages'] as $language) {

  $exclude_fields[] = 'text_txt_' . $language;
  $exclude_fields[] = 'content_txt_' . $language;
  $exclude_fields[] = 'title_txt_' . $language;
  $exclude_fields[] = 'description_txt_' . $language;
  $exclude_fields[] = 'hashtag_ss_txt_' . $language;

}

$exclude_fields_prefixes = array('etl_');

$exclude_fields_suffixes = array('_uri_ss', '_preflabel_and_uri_ss');

$fields = get_fields($doc, $exclude_fields, $exclude_fields_prefixes, $exclude_fields_suffixes);

?>
<div id="results" class="row">

  <div class="date row">
    <?= $datetime ?>
  </div>

  <div class="row">
		<span class="uri">
		
				<?php
        if ($deepid) {
          ?>
          <?php if ($deep_uri_tip) { ?>
            <span data-tooltip class="has-tip" title="<?= $deep_uri_tip ?>">
          <?php } ?>
          <?= $deep_uri_label ?>
          <?php if ($deep_uri_tip) { ?>
            </span>
          <?php } ?>
          in
          <?php
        } // if deepid
        ?>

      <?php if ($uri_tip) { ?>
      <span data-tooltip class="has-tip" title="<?= $uri_tip ?>">
				<?php } ?>
        <?= $uri_label ?>
        <?php if ($uri_tip) { ?>
					</span>
    <?php } ?>
		</span>
    <?php if ($file_size_txt) { ?>
      <span class="size">(<?= $file_size_txt ?>)</span>
    <?php } // if filesize?>
  </div>


  <?php if ($author) {
    print '<div class="author row">' . $author . ':</div>';
  } ?>


  <div class="row">
    <h1><a class="title" target="_blank" href="<?= $uri ?>"><?= $title ?></a>
    </h1>
  </div>

  <hr/>

  <div class="row">


    <ul class="tabs" data-tabs id="preview-tabs">

      <?php if ($preview_image) { ?>

        <li class="tabs-title is-active"><a href="#preview-image"
                                            aria-selected="true">Preview</a>
        </li>
        <li class="tabs-title"><a href="#preview-content">Text</a></li>

        <?php
// if preview image
      }
      else { ?>
        <li class="tabs-title is-active"><a href="#preview-content"
                                            aria-selected="true">Text</a></li>
      <?php } // no preview image ?>

      <?php if ($ocr) { ?>
        <li class="tabs-title"><a href="#preview-ocr">OCR</a></li>

      <?php } // if ocr ?>
      <li class="tabs-title"><a href="#preview-meta">Meta</a></li>
    </ul>
  </div>

  <div class="tabs-content" data-tabs-content="preview-tabs">
    <?php if ($preview_image) { ?>

    <div class="tabs-panel is-active" id="preview-image">
      <div class="panel">
        <a href="<?= $id ?>" target="_blank"><img src="<?= $preview_image ?>"/></a>
      </div>

    </div>
    <div class="tabs-panel" id="preview-content">
      <?php
      // if preview image
      } else { ?>
      <div class="tabs-panel is-active" id="preview-content">
        <?php } // no preview image ?>

        <div id="content">


          <?php

          if ($preview_allowed) {

            // if image
            if (strpos($type, 'image') === 0) { ?>
              <a href="<?= $id ?>" target="_blank"><img src="<?= $id ?>"/></a>
              <?php
            } // if image

            // if video
            if (strpos($type, 'video') === 0) { ?>
              <video controls="controls" src="<?= $id ?>"></video>
              <?php
            } // if video

            // if audio
            if (strpos($type, 'audio') === 0) { ?>
              <audio controls="controls" src="<?= $id ?>"></audio>
              <?php
            } // if audio ?>


            <?php if ($type == 'CSV row' || $type =='Knowledge graph') {

				  print '<div class="graph">';

              foreach ($fields as $field) {
                if ($field != 'id' and $field != 'content_type' and $field != 'content_type_group' and $field != 'container_s' and isset($doc->$field)) {

						  print '<div>';
                    print "<h2 title=\"" . htmlentities($field) . "\">" . htmlentities(get_label($field)) . '</h2>';


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
										$value_uri = '?view=preview&q=id:' . urlencode($value_uri);

	                       		print '<li class="entity"><a target="_blank" href="'. $value_uri . '">' . htmlspecialchars($label) . '</a></li>';
	                       		$linked_values[] = $label;
									} else {
	                       		print '<li class="entity">' . htmlspecialchars($value) . '</li>';
              		       		$linked_values[] = $value;				
									}
                      	}
                      	print '</ul>';
						  }

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

            ?>

            <?= $content ?>

            <?php
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

      <?php if ($ocr && $preview_allowed) { ?>
        <div class="tabs-panel" id="preview-ocr">

          <div id="ocr"><?php
            print "<i>" . t("content_ocr") . "</i><br/>";
            ?>
            <?= $ocr ?>
          </div>
        </div>
      <?php } // if ocr ?>
      <div class="tabs-panel" id="preview-meta">
        <div class="meta">
          <?php
          foreach ($fields as $field) {
            if (isset($doc->$field)) {
              ?>
              <div>
                <?php
                print "<b><span title=\"" . htmlentities($field) . "\">" . htmlentities(get_label($field)) . '</span></b>:<br />';

                if (is_array($doc->$field)) {
                  print "<ul>";
                  foreach ($doc->$field as $value) {
                    print '<li>' . htmlspecialchars($value) . '</li>';

                  }
                  print "</ul>";
                }
                else {
                  print htmlspecialchars($doc->$field);
                }


                print '<br /><br />';
                ?>
              </div>
              <?php
            }
          }
          ?>
        </div>

      </div>


    </div>

    <hr/>

    <div class="commands">
      <a target="_blank"
         href="<?= $uri ?>"><?php echo t('open'); ?></a> <?php if ($cfg['metadata']['server']) { ?> |
        <a target="_blank" title="<?php echo t('meta description'); ?>"
           href="<?php print get_metadata_uri($cfg['metadata']['server'], $id); ?>"><?php echo t('meta'); ?></a> <?php } ?>
    </div>

    <?php
    } // foreach doc
    ?>

  </div>
