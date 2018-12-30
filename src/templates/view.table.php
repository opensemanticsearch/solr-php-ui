<div class="table-scroll">
  <?php

  // View results as table with sortable columns

  // todo: exclude path*

  // todo: if sort spalte, dann diese in tabellenansicht rein, auch wenn kein feld (falls sort asc am anfang ohne werte und sortierung nicht umdrehbar)


  // Find all columns (=fields)
  $fields = array();

  $exclude_fields = array('_version_','_text_', 'content_txt', 'preview_s');

  // exclude fields that are only copied for language specific analysis in index
  foreach ($cfg['languages'] as $language) {

    $exclude_fields[] = 'text_txt_' . $language;
    $exclude_fields[] = 'content_txt_' . $language;
    $exclude_fields[] = 'title_txt_' . $language;
    $exclude_fields[] = 'description_txt_' . $language;
    $exclude_fields[] = 'hashtag_ss_txt_' . $language;

  }

  foreach ($results->response->docs as $doc) {
    foreach ($doc as $field => $value) {

      $exclude = FALSE;
      if (in_array($field, $exclude_fields)) {
        $exclude = TRUE;
      }

      // if not yet there and not excluded, include field to cols
      if (!in_array($field, $fields) && $exclude == FALSE) {
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
    if ($sort == $field . ' asc' or $sort == $field . ' desc') {
      print '<b>';
    }

    // build sorting link and print linked column name
    if ($sort == $field . ' asc') {
      print '<a title="' . htmlentities($field) . '" onclick="waiting_on();" href="' . buildurl($params, "sort", $field . ' desc', 's', 1) . '">' . htmlentities(t($field)) . '</a>';
    }
    else {
      print '<a title="' . htmlentities($field) . '" onclick="waiting_on();" href="' . buildurl($params, "sort", $field . ' asc', 's', 1) . '">' . htmlentities(t($field)) . '</a>';
    }

    if ($sort == $field . ' asc' or $sort == $field . ' desc') {
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

    $highlightfield = 'content';

    // if highligting available for the language, use highlighted content
    foreach ($cfg['languages'] as $language) {
      $language_specific_fieldname = 'content_txt_' . $language;
      if (isset($results->highlighting->$id->$language_specific_fieldname)) {
        $highlightfield = $language_specific_fieldname;
      }
    }

    if (isset($results->highlighting->$id->$highlightfield)) {
      foreach ($results->highlighting->$id->$highlightfield as $snippet) {
        print '<li class="snippet">' . $snippet . '</li>';
      }
    }

    print '</ul></td>';

    foreach ($fields as $field) {
      print '<td>';
      if (isset($doc->$field)) {

        if (is_array($doc->$field)) {
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
