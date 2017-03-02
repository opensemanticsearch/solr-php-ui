	<?php 
	
	// if no results, show message
	if ($total == 0) {
		?>
	<div id="noresults" class="panel">


		<?php
		if ($error) {
			print '<p>Error: </p><p>' . $error . '</p>';
		} else {
			// Todo: Vorschlag: (in allen Bereichen, Ähnliches)
			print t('No results');
		}
		?>
	</div>

	<?php
	} // total == 0
	else { // there are results documents
		
		if ($error) {
			print '<p>Error:</p><p>' . $error . '</p>';
		}
		
		// print the results with selected view template
		if ($view == 'list') {
			include 'templates/pagination.php';
			include 'templates/view.list.php';
			include 'templates/pagination.php';
				
		} elseif ($view == 'preview') {

			include 'templates/view.preview.php';

		} elseif ($view == 'images') {
				
			include 'templates/view.images.php';

		} elseif ($view == 'videos') {
			include 'templates/view.videos.php';
		} elseif ($view == 'table') {
			include 'templates/view.table.php';
		} elseif ($view=='words') {

			include 'templates/view.words.php';

		} elseif ($view=='trend') {

			include 'templates/view.trend.php';

		} elseif ($view == 'timeline') {

			include 'timeline.php';

		}
		else {
			include 'templates/pagination.php';
			include 'templates/view.list.php';
			include 'templates/pagination.php';
				
		}


	} // if total <> 0: there were documents
	?>