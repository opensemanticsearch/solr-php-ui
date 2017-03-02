<div class="table-scroll">
<?php

// View results as table with sortable columns

// todo: exclude path*

// todo: if sort spalte, dann diese in tabellenansicht rein, auch wenn kein feld (falls sort asc am anfang ohne werte und sortierung nicht umdrehbar)



// Find all columns (=fields)
$cols=array();

foreach ($results->response->docs as $doc) {
	foreach ($doc as $field => $value) {
		
		if (!in_array($field, $fields)
				and $field!='_text_'
				and $field!='stemmed'
				and $field!='_version_'
				and $field!='content'
				and $field!='author'
		) {
			
			$fields[] = $field;
		};

	}
}
asort($fields);


// Table header and col names

print '<table><thead><tr>'; // Temporary Style displays table in foreground

print '<td>Query matchings</td>';

foreach ($fields as $field) {
	// todo: sort link asc/desc

	print '<th>';
	
	// if col is sorting col, highlight it
	if ($sort==$field.' asc' or $sort==$field.' desc') {
		print '<b>';
	}

	// build sorting link and print linked column name
	if ($sort==$field.' asc') {
		print '<a title="'.htmlentities($field).'" onclick="waiting_on();" href="'.buildurl($params,"sort",$field.' desc','s',1).'">'.htmlentities( t($field) ).'</a>';
	}
	else {print '<a title="'.htmlentities($field).'" onclick="waiting_on();" href="'.buildurl($params,"sort",$field.' asc','s',1).'">'.htmlentities( t($field) ).'</a>';
	}

	if ($sort==$field.' asc' or $sort==$field.' desc') {
		print '</b>';
	}

	print '</th>';

}

print '</tr></thead><tbody>';

// print documents as rows
foreach ($results->response->docs as $doc) {
	print '<tr>';

	print '<td><ul>';
	// highlightings (matching parts of content)
	$id = $doc->id;
	if (isset($results->highlighting->$id->$highlightfield)) {
		foreach ($results->highlighting->$id->$highlightfield as $snippet) {
			print '<li class="snippet">' . $snippet . '</li>';
		}
		
	}
	print '</ul></td>';
	
	foreach ($fields as $field) {
		print '<td>';
		if (isset($doc->$field)) {
						
			if ( is_array ( $doc->$field ) ) {
				foreach ($doc->$field as $value) {
					print '<li>' . htmlspecialchars($value) . '</li>';
				}
					
			} 
			else {
				print htmlspecialchars($doc->$field);
			}
		
		}
		print '</td>';
	}
	
	
	print '</tr>';
}

print '</tbody></table>';
?>

</div>