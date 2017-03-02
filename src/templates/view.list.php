<?php
// Standard view
//
// Show results as list

?>

<div id="results" class="row">
    <ul class="no-bullet">

<?php
// todo: bei table alle fields ausser content?! und erst felder sammeln, dann html tabelle und for each field if exist
foreach ($results->response->docs as $doc) {

  $id = $doc->id;
  
  // Type
  $type = $doc->content_type; // todo: contentype schoener mit wertearray
  
  // URI
  
  // if part of container like zip, link to container file
  // if PDF page URI to Deeplink
  // since PDF Reader can open deep links
  if (isset($doc->container_s) and $type!='PDF page') {
  	$uri = $doc->container_s;
  	$deepid = $id;
  
  }
  else {
  	$uri = $id;
  	$deepid = false;
  }
  
  $uri_label = $uri;
  $uri_tip = false;
  
  // if file:// then only filename
  if (strpos ($uri, "file://")==0) {
  	$uri_label = basename($uri);
  	 
  	// for tooptip remove file:// from beginning
  	$uri_tip = substr( $uri, 7 );
  	$uri_tip = htmlspecialchars($uri_tip);
  	 
  }

  if ($deepid) {
  	$deep_uri_label = $deepid;
  	$deep_uri_label = htmlspecialchars($deep_uri_label);
  	
  	$deep_uri_tip = false;
	// if file:// then only filename
  	if (strpos ($deepid, "file://")==0) {
  		$deep_uri_label = basename($deepid);
  		$deep_uri_label = htmlspecialchars($deep_uri_label);
  		
  		// for tooltip remove file:// from beginning
  		$deep_uri_tip = substr( $deepid, 7 );
  		$deep_uri_tip = htmlspecialchars($deep_uri_tip);
  		
  	}
  }
  
  $uri_unmasked = $uri;
  $uri = htmlspecialchars($uri);
  $uri_label = htmlspecialchars($uri_label);
  
  
  // Author
  $author = htmlspecialchars($doc->author_s);

  // Title
  $title = t('No title');
  if (isset($doc->title)) {
    if (!empty($doc->title)) {
    	$title = htmlspecialchars($doc->title);
    }
  }

  // Modified date
  if (isset($doc->file_modified_dt)) {
	$datetime = $doc->file_modified_dt; 
  } elseif (isset($doc->last_modified)) {
  	$datetime = $doc->last_modified;
  } else {
  	$datetime = false;
  }


  $file_size=0;
  $file_size_txt = '';	
  // File size
  if (isset($doc->file_size_i)) {
  	$file_size = $doc->file_size_i;
  	$file_size_txt = filesize_formatted($file_size);
  }
  
  
  
  // Snippet
  //print_r($results->highlighting->$id);
  
  $snippets = array('...');
  if (isset($results->highlighting->$id->$highlightfield)) {
    $snippets = $results->highlighting->$id->$highlightfield;
  } elseif(isset($doc->content)) {
  	// if no snippets available, use content as snippet
	$snippets = array($doc->content);
	// and cut it to snippet size
	if (strlen($snippets[0]) > $cfg['snippetsize']) {
		$snippets[0] = substr($snippets[0],0,$cfg['snippetsize'])."...";
	}
  }

?>
<li>

	<div class="title"><a class="title" target="_blank" href="<?=$uri?>"><?=$title?></a></div>
	
	<div class="date"><?=$datetime?></div>
	
	<div>
		<span class="uri">

		<?php 
		if ($deepid) {
		?>
					<?php if ($deep_uri_tip) { ?>
					<span data-tooltip class="has-tip" title="<?=$deep_uri_tip?>">
				<?php } ?>
			<?=$deep_uri_label?>
				<?php if ($deep_uri_tip) { ?>
					</span>
				<?php } ?>
in
		<?php 
		} // if deepid
		?>
		
		<?php if ($uri_tip) { ?>
					<span data-tooltip class="has-tip" title="<?=$uri_tip?>">
				<?php } ?>
			<?=$uri_label?>
				<?php if ($uri_tip) { ?>
					</span>
				<?php } ?>
		</span>
		<?php if ($file_size_txt) { ?>
		<span class="size">(<?=$file_size_txt?>)</span>
		<?php } // if filesize?>
	</div>
	
	
	<div class="snippets">
		<?php if ($author) { print '<div class="author">'.$author.'</div>'; } ?>
		<ul>
<?php

	foreach ($snippets as $snippet) {
		print '<li class="snippet">' . $snippet . '</li>';
	}

?>
		</ul>
	</div>
	
	
	<?php 		
				$first = true;	
				// Print all configurated facets, but the field of result, not the facet of all results
				foreach ($cfg['facets'] as $field => $facet_config) {

					
					if ($field != '_text_' and $field !='author_s') {
						if ( isset( $doc->$field ) ) {
							
							if ($first) {
								
								?>
								<span title="Extracted named entities or annotated tags">Entities:</span>
								<?php 
								
							}
								
							?>
							
									<span class="<?= $field ?>"><?php 

								
										if ( is_array ( $doc->$field ) ) {
											foreach ($doc->$field as $value) {
												if ($first) {$first=false;} else { print ', '; }
												print htmlspecialchars($value);
											}
									
										} else {
											if ($first) {$first=false;} else { print ', '; }
											print $doc->$field;
										}
										?>
									</span>
									<?php
							}
					}		
				}

	?>
	
	
	<div class="commands">
		<a target="_blank" href="<?=$uri?>"><?php echo t('open'); ?></a> <?php if ($cfg['metadata']['server']) { ?> | <a target="_blank" title="<?php echo t('meta description'); ?>" href="<?php print get_metadata_uri ($cfg['metadata']['server'], $uri_unmasked); ?>"><?php echo t('meta'); ?></a> <?php } ?> | <?php print '<a target="_blank" href="preview.php?id='.urlencode($uri_unmasked).'">' . t('Preview') . '</a>'; ?>
	</div>
</li>

<?php
  } // foreach doc
?>

    </ul>
    
</div>

