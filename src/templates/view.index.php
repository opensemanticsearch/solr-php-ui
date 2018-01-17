<html>
<head>
  <title><?php

    echo t('Search');
    if ($query) {
      print ' ' . htmlspecialchars($query);
    }

    ?></title>


  <link rel="stylesheet" href="css/foundation.css">

  <script src="js/vendor/jquery.js"></script>
  <script src="js/vendor/what-input.js"></script>
  <script src="js/vendor/foundation.js"></script>
  <script src="js/app.js"></script>

  <script type="text/javascript" src="jquery/jquery.autocomplete.js"></script>
  <script type="text/javascript" src="autocomplete.js"></script>
  <link rel="stylesheet" href="css/app.css" type="text/css"/>
</head>
<body>
<?php

if (file_exists("templates/custom/view.index.topbar.php")) {
  include "templates/custom/view.index.topbar.php";
}
else {
  include "templates/view.index.topbar.php";
}
?>

<div class="row">

  <div id="searchform-wrapper" class="small-12 medium-8 large-9 columns">
    <?php
    /*
     * SearchForm
    */
    ?>

    <form id="searchform" accept-charset="utf-8" method="get">

      <?php echo $form_hidden_parameters ?>
      <div id="search-field" class="small-12 medium-8 large-8 columns">
        <input id="q" name="q" type="text"
               value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
      </div>


      <div id="search-button" class="small-12 medium-2 large-2 columns">

        <input id="submit" type="submit" class="button postfix"
               value="<?php echo t("Search"); ?>" onclick="waiting_on()"/>
      </div>

      <button type="button" data-toggle="searchoptions">Search options</button>


      <div class="callout secondary small-12 columns" id="searchoptions"
           data-toggler data-animate="slide-in-left slide-out-left">

        <div class="row">
          <div class="small-12 columns">
            <h4>Search options</h4>
          </div>
        </div>

        <div class="row">
          <div id="search-op" class="small-12 columns">
            <fieldset class="fieldset">
              <legend>Search operator:</legend>
              <div class="small-12 large-12 columns">
                Find:
              </div>

              <div class="small-12 large-4 columns">
                <input type="radio"
                       name="operator" <?php if ($operator == 'OR') {
                  print " checked";
                } ?> value="OR"> At least one word (OR)
              </div>
              <div class="small-12 large-4 columns">
                <input type="radio"
                       name="operator" <?php if ($operator == 'AND') {
                  print " checked";
                } ?> value="AND"> All words (AND)
              </div>
              <div class="small-12 large-4 columns">
                <input type="radio"
                       name="operator" <?php if ($operator == 'Phrase') {
                  print " checked";
                } ?> value="Phrase"> Exact expression (Phrase)
              </div>
            </fieldset>
          </div>
        </div>


        <div class="row">
          <div class="small-12 columns">
            <fieldset class="fieldset">
              <legend>Semantic search &amp; fuzzy search</legend>
              <div class="small-12 large-12 columns">
                Also find:
              </div>
              <div class="small-12 large-6 columns">
                <input type="checkbox" name="stemming" id="stemming"
                       value="stemming"<?php if ($stemming == TRUE) {
                  print " checked";
                } ?>><label for="stemming">Other word forms (grammar &amp;
                  stemming)</label>
              </div>
              <div class="small-12 large-6 columns">
                <input type="checkbox" name="synonyms" id="synonyms"
                       value="synonyms"<?php if ($synonyms == TRUE) {
                  print " checked";
                } ?>><label for="synonyms">Synonyms & aliases</label>
              </div>

            </fieldset>
          </div>
        </div>

      </div>

    </form>


  </div>


</div>


<div class="row">

  <div id="main" class="small-12 medium-8 large-9 columns">


    <?php

    include 'templates/select_view.php';

    ?>


    <?php

    // if no results, show message
    if ($total == 0) {
      ?>
      <div id="noresults" class="panel">


        <?php
        if ($error) {
          print '<p>Error: </p><p>' . $error . '</p>';
        }
        else {
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

        include 'templates/select_sort.php';

        include 'templates/pagination.php';
        include 'templates/view.list.php';
        include 'templates/pagination.php';

      }
      elseif ($view == 'preview') {

        include 'templates/pagination.php';
        include 'templates/view.preview.php';
        include 'templates/pagination.php';

      }
      elseif ($view == 'images') {
        include 'templates/select_sort.php';

        include 'templates/pagination.php';
        include 'templates/view.images.php';
        include 'templates/pagination.php';

      }
      elseif ($view == 'videos') {
        include 'templates/select_sort.php';

        include 'templates/pagination.php';
        include 'templates/view.videos.php';
        include 'templates/pagination.php';

      }
      elseif ($view == 'audios') {
        include 'templates/select_sort.php';

        include 'templates/pagination.php';
        include 'templates/view.audios.php';
        include 'templates/pagination.php';

      }
      elseif ($view == 'table') {

        include 'templates/pagination.php';
        include 'templates/view.table.php';
        include 'templates/pagination.php';

      }
      elseif ($view == 'words') {

        include 'templates/view.words.php';
      }
      elseif ($view == 'graph') {

        include 'templates/view.graph.php';


      }
      elseif ($view == 'trend') {

        include 'templates/view.trend.php';

      }
      elseif ($view == 'timeline') {

        include 'templates/pagination.php';
        include 'timeline.php';
        include 'templates/pagination.php';

      }
      else {

        include 'templates/pages.php';
        include 'templates/view.list.php';
        include 'templates/pages.php';

      }


    } // if total <> 0: there were documents
    ?>

  </div><?php // main ?>


  <div id="sidebar" class="small-12 medium-4 large-3 columns">

    <?php
    // if preview, schow metadata
    if ($view == "preview") {
      include "templates/view.preview.sidebar.php";
    }
    else {
      // show facets
      include "templates/view.facets.php";
    }
    ?>

  </div>


</div>

<?php // Wait indicator - will be activated on click = next search (which can take a while and additional clicks would make it worse) ?>
<div id="wait">
  <img src="images/ajax-loader.gif">
  <p><?php echo t('wait'); ?></p>
</div>

<script type="text/javascript">

  /*
  * init foundation responsive framework
  */
  $(document).foundation();

</script>


<script type="text/javascript">

  /*
  * untoggle advanced search options on load
  */


  // we dont want to see animation on initial toggle off on load
  $("#searchoptions").hide();


</script>

<script type="text/javascript">

  /*
   * display wait-indicator
   */
  function waiting_on() {
    document.getElementById('wait').style.visibility = "visible";
    return true;
  }
</script>


</body>
</html>
