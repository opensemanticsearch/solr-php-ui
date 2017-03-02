<?php

// print a facet and its values as links
function print_field(&$doc, $field, $label) {
	global $params;

	if ( isset( $doc->$field ) ) {

		?>
			<div id="<?= $field ?>" class="facet">
				<h2>
					<?= $label ?>
				</h2>
				<ul class="no-bullet">
				<?php
				if ( is_array ( $doc->$field ) ) {
					foreach ($doc->$field as $value) {
						print '<li>' . htmlspecialchars($value) . '</li>';
					} 
			
				} else {
					print $doc->$field;
				}
				?>
				</ul>
			</div>
			<?php
	}
}

?>
<div id="facets">
	

				<?php if ($file_size_txt) { ?>
				<div id="file_modified_dt" class="facet">
				<h2>
					<?php echo t('File date');  ?>
				</h2>

				
				<ul class="no-bullet">
				
						<li><?=$datetime?></li>
					
				    </ul>
				    
				</div>
				<?php } // if filedate ?>
				
				
				
				<?php if ($file_size_txt) { ?>
				<div id="facet_file_size" class="facet">
				<h2>
					<?php echo t('file_size');  ?>
				</h2>

				
				<ul class="no-bullet">
				
						<li><span class="size"><?=$file_size_txt?></span></li>
					
				    </ul>
				    
				</div>
				
				<?php } // if filesize?>
				
				
				
				<?php 
				
				// print contenttype
				print_field($doc, "content_type", t('Content type') );
				
				
				
				// Print all configurated facets, but the field of result, not the facet of all results
				foreach ($cfg['facets'] as $facet => $facet_config) {

					if ($facet != 'text') print_field($doc, $facet, t($facet_config['label']) );
				}

				?>

</div>