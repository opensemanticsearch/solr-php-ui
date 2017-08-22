<?php

// Show preview


// todo: bei table alle fields ausser content?! und erst felder sammeln, dann html tabelle und for each field if exist
foreach ($results->response->docs as $doc) {
  $id = $doc->id;

  
  // Type
  $type= $doc->content_type; // todo: contentype schoener mit wertearray
  
  
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
  }
  
  if ($deepid) {
  	$deep_uri_label = $deepid;
  	$deep_uri_tip = false;
  	// if file:// then only filename
  	if (strpos ($deepid, "file://")==0) {
  		$deep_uri_label = basename($deepid);
  		// for tooptip remove file:// from beginning
  		$deep_uri_tip = substr( $deepid, 7 );
  	}
  }
  
  
  // Author
  $author = htmlspecialchars($doc->author_s);
  // Title
  $title="Ohne Titel";
  if (isset($doc->title)) {
    if (!empty($doc->title)) {
    	$title= htmlspecialchars($doc->title);
    }
  }

  // Modified date
  if (isset($doc->file_modified_dt)) {
	$datetime = $doc->file_modified_dt; 
  } elseif (isset($doc->last_modified)) {
  	$datetime = $doc->last_modified;
  } else {
  	$datetime=false;
  }
  
  // File size
  $file_size=0;
  $file_size_txt = '';
  if (isset($doc->file_size_i)) {
  	$file_size = $doc->file_size_i;
  	$file_size_txt = filesize_formatted($file_size);
  }

  $preview_allowed = true;

  # if not allowed in config
  if (isset($cfg['preview_allowed'])) {
  	if (!$cfg['preview_allowed'])
  		$preview_allowed = false;
  }
  
  # but ok, if allowed in this document
  if (isset($doc->preview_allowed_b)) {
	if ($doc->preview_allowed_b == true) {
		$preview_allowed = true;
	} else {
		$preview_allowed = false;
	}
  }
    
  if ($preview_allowed) {
  	//Content
  	if (isset($results->highlighting->$id->content)) {
  		$content = $results->highlighting->$id->content[0];
 	  		
  	} else {
  		$content = $doc->content;
  		$content = htmlspecialchars( $content );
  	}
  	
  	$content = strip_empty_lines($content, 3);
  	$content = nl2br($content); // new lines
  	 
  	
  	if (isset($cfg['preview_path']) and isset($doc->preview_s)) {
  		$preview_image = $cfg['preview_path'] . $doc->preview_s;
  	}

  }  else {
	$content = t('preview_not_allowed');
  }
  
  
  //OCR
  $ocr = false;
  if (isset($doc->ocr_t) && $preview_allowed) {
  	$ocr = strip_empty_lines($doc->ocr_t, 3);
  	$ocr = htmlspecialchars($ocr);
  	$ocr = nl2br($ocr); // new lines
  	if ($ocr == "") $ocr=false;
  }
  

  
  
  // Find all columns (=fields)
  $cols=array();
  
  foreach ($doc as $field => $value) {
  	if (!in_array($field, $cols) and $field!='_version_' and $field!='content' and $field!='preview_s'  and $field!='ocr_t') {
  		$cols[] = $field;
  	};
  }
  
  asort($cols);

?>
<div id="results" class="row">

	<div class="date row">
		<?=$datetime?>
	</div>

	<div class="row">
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
	
	
	<?php if ($author) { print '<div class="author row">'.$author.':</div>'; } ?>

	
	
<div class="row">
	<h1><a class="title" target="_blank" href="<?=$uri ?>"><?=$title ?></a></h1>
	</div>
	
	<hr />

	<div class="row">


<ul class="tabs" data-tabs id="preview-tabs">

<?php if ($preview_image) { ?>

<li class="tabs-title is-active"><a href="#preview-image" aria-selected="true">Preview</a></li>
<li class="tabs-title"><a href="#preview-text">Text</a></li>

<?php
// if preview image
} else {	?>
<li class="tabs-title is-active"><a href="#preview-text" aria-selected="true">Text</a></li>
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
	<a href="<?=$id?>" target="_blank"><img src="<?=$preview_image?>" /></a>
	</div>
	
</div>
<div class="tabs-panel" id="preview-text">
<?php
// if preview image
} else {	?>
<div class="tabs-panel is-active" id="preview-text">
<?php } // no preview image ?>

	<div id="content">
	
	
	
		<?php
		
	if ($preview_allowed) {
		
		// if image
		if (strpos ($type, 'image') === 0) { ?>
		<a href="<?=$id?>" target="_blank"><img src="<?=$id?>" /></a>
		<?php
		} // if image
	
		// if video
		if (strpos ($type, 'video') === 0) { ?>
		<video src="<?=$id?>"></video>
		<?php
		} // if video

		// if audio
		if (strpos ($type, 'audio') === 0) { ?>
		<audio controls="controls" src="<?=$id?>"></audio>
		<?php
		} // if audio ?>
	

		<?php if ($type=='CSV row') {
		
		foreach ($cols as $col) {
			if ($col!='id' and $col!='content_type' and $col!='container_s' and isset($doc->$col)) { ?>
				<div>
			<?php 
				print "<b>" . htmlentities($col) . '</b>:<br />';

				if ( is_array ( $doc->$col ) ) {
					print "<ul>";
					foreach ($doc->$col as $value) {
						print '<li>' . htmlspecialchars($value) . '</li>';

					}
					print "</ul>";
				}	
				else {
					print htmlspecialchars($doc->$col);
				}
					
			
				print '<br /><br />';
			?>
			</div>		
			<?php
		}
}

} // if csv row
	
	?>
	
		<?=$content?>

		<?php		
		if ($ocr) {
			print "<b>" . t("content_ocr") ."</b><br/>";
			print $ocr;
		}
		
		
	} // preview allowed
	else {
		print ( t('preview_not_allowed') );
	}
		?>
		
		
	</div>
</div>

<?php if ($ocr && $preview_allowed) { ?>
<div class="tabs-panel" id="preview-ocr">

	<div id="ocr"><?php 
		print "<i>" . t("content_ocr") ."</i><br/>";
		?>
		<?=$ocr?>
	</div>
</div>
<?php } // if ocr ?>
<div class="tabs-panel" id="preview-meta">
	<div class="meta">
<?php 
foreach ($cols as $col) {
		if (isset($doc->$col)) {
			?>
			<div>
			<?php 
			print "<b>" . htmlentities($col) . '</b>:<br />';

				if ( is_array ( $doc->$col ) ) {
					print "<ul>";
					foreach ($doc->$col as $value) {
						print '<li>' . htmlspecialchars($value) . '</li>';

					}
					print "</ul>";
				}	
				else {
					print htmlspecialchars($doc->$col);
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

	<hr />
	
	<div class="commands">
		<a target="_blank" href="<?=$uri?>"><?php echo t('open'); ?></a> <?php if ($cfg['metadata']['server']) { ?> | <a target="_blank" title="<?php echo t('meta description'); ?>" href="<?php print get_metadata_uri ($cfg['metadata']['server'], $id); ?>"><?php echo t('meta'); ?></a> <?php } ?>
	</div>

<?php
  } // foreach doc
?>

</div>
