<?php

if ($count_open_etl_tasks_extraction > 0) {

?>
<div class="row">
  <div  class="callout secondary">
    <p>Import status: <strong>Running file import (still <?= $count_open_etl_tasks_extraction ?> documents to process)</strong></p>
    <p>Because of yet running and open tasks like text extraction and analysis maybe not all results were found yet, since at the moment of this search <?= $count_open_etl_tasks_extraction ?> file(s) could be only searched, overviewed and filtered by their file names only, not yet by their content and/or content based facets/filters!</p>
  </div>
</div>
<?php

}

?>