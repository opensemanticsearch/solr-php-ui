<?php
// Show results as trend chart

?>


<?php
// Include libs
?>

<script src="d3js/d3.min.js"></script>
<script src="nvd/nv.d3.js"></script>
<link href="nvd/nv.d3.css" rel="stylesheet">


<?php

// Page

if ($date_label) {
  echo '<h1>' . $date_label . '</h1>';
}

?>

<div id='chart'>
  <svg style='height:500px;'></svg>
</div>


<?php

// Init with size parameters

?>
<script type="text/javascript">
  nv.addGraph(function () {
    var chart = nv.models.discreteBarChart()
      .x(function (d) {
        return d.label
      })    //Specify the data accessors.
      .y(function (d) {
        return d.value
      })
      .staggerLabels(true)    //Too many bars and not enough room? Try staggering labels.
      .tooltips(false)        //Don't show tooltips
      .showValues(true)       //...instead, show the bar value right on top of each bar.
      .transitionDuration(350)
    ;

    d3.select('#chart svg')
      .datum(Data())
      .call(chart);

    nv.utils.windowResize(chart.update);

    return chart;
  });

  //Each bar represents a single discrete quantity.
  function Data() {
    return [
      {
        key: "Cumulative Return",
        values: [



          <?php

          // Data2JSON

          $first = TRUE;
          $pre_val = 0;
          foreach ($datevalues as $value) {
            if($value['count'] == $pre_val){
								$prev_val = $value['count'];
								continue;
							}

            // todo: link the chart
            $link = '';

            // if not first entry, print delimiting comma
            if ($first) {
              $first = FALSE;
            }
            else {
              print ",\n";
            }

            print '{ "label": "' . $value['label'] . '", "value": ' . $value['count'] . ', "link": "' . $value['link'] . '" }';
          }
          ?>


        ]
      }
    ]

  }


</script>
