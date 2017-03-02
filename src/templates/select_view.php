<?php ?>

<div id="select_view" class="row">

<div class="button-group">
<?php

// Show view selector (list, image-gallery, table and so on)

?>
<?php
		
		//echo t('View').': ';
		
		if ($view == 'list') {
			print '<a class="button secondary active" href="#">' . t('List') . '</a>';
		} else {
			if ($view == "preview") {
				// if switching from preview mode to list, dont reset start to first result
				// but on a regular page
				$pagestart = floor($start / $limit) * limit;
				$link = buildurl($params, "view", '', 's', $pagestart);
			} else { // switching from other view like images or table, so reset start to first result
				$link = buildurl($params, "view", '', 's', 1);
			}
			print '<a class="button secondary" onclick="waiting_on();" href="' . $link . '">' . t('List') . '</a>';

		}

		if ($view == 'preview') {
			print '<a class="button secondary active" href="#">'.t('Preview').'</a>';
		} else {
			print '<a class="button secondary" onclick="waiting_on();" href="' . buildurl($params, "view", 'preview') . '">' . t('Preview') . '</a>';
		}

		if ($view == 'images') {
			print '<a class="button secondary active" href="#">'.t('Images').'</a>';
		} else {
			print '<a class="button secondary" onclick="waiting_on();" href="' . buildurl($params, "view", 'images', 's', 1) . '">' . t('Images') . '</a>';
		}
		
		if ($view == 'videos') {
			print '<a class="button secondary active" href="#">'.t('Videos').'</a>';
		} else {
			print '<a class="button secondary" onclick="waiting_on();" href="' . buildurl($params, "view", 'videos', 's', 1) . '">' . t('Videos') . '</a>';
		}
		
		if ($view == 'table') {
			print '<a class="button secondary active" href="#">'.t('Table').'</a>';
		} else {
			print '<a class="button secondary" onclick="waiting_on();" href="' . buildurl($params, "view", 'table', 's', 1) . '">' . t('Table') . '</a>';
		}

		if ($view=='trend') {
			print '<a class="button secondary active" href="#">'.t('view_trend').'</a>';
		} else {
			print '<a class="button secondary" onclick="waiting_on();" href="'.buildurl($params,"view",'trend','s', false).'">'.t('view_trend').'</a>';
		}
				
		
		
		?>
		
		<button class="button secondary dropdown" type="button" data-toggle="analyze-dropdown"><?php echo t("view_analytics"); ?></button>

		<div class="dropdown-pane" id="analyze-dropdown" data-dropdown data-auto-focus="true">
				
		<?php 
		if ($view=='words') {
			print '<a class="button active" href="#">'.t('view_words').'</a>';
		} else {
			print '<a class="button" onclick="waiting_on();" href="'.buildurl($params,"view",'words','s', false).'">'.t('view_words').'</a>';
		}
		?><hr/><?php
				
		if ($view == 'entities') {
			print '<a class="button active" href="#">'.t('Named entities').'</a>';
		} else {
			print '<a class="button" onclick="waiting_on();" href="' . buildurl($params, "view", 'entities', 's', 1) . '">' . t('Named entities') . '</a>';
		}
		
		?><hr/><?php
				
		if ($view=='graph') {
			print '<a class="button active" href="#">'.t('Connections').'</a>';
		} else {
			print '<a class="button" onclick="waiting_on();" href="'.buildurl($params,"view",'graph','s', false).'">'.t('Connections').'</a>';
		}
		
		
?>
</div>

</div>

</div>
