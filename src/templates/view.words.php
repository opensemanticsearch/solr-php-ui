<?php
// Show results as word cloud
$words = [];
$total = 0;
if (!empty($results->facet_counts->facet_fields)) {
  foreach ($results->facet_counts->facet_fields->_text_ as $word => $count) {
    $link = buildurl_addvalue($params, '_text_', $word, 's', 1);
    $words[] = ['text' => $word, 'size' => $count, 'link' => $link];
    $total += $count;
  }
}
// Now we have a total count, make size a percentage.
foreach ($words as &$word) {
  $word['size'] = ceil(($word['size'] * 100) / $total);
}
?>

<script src="d3js/d3.min.js" charset="utf-8"></script>
<script src="d3js/d3.layout.cloud.js" charset="utf-8"></script>

<div id="results" class="row">
  <div class="doc-count">
    <i>Documents: <?= $total ?></i>
  </div>

  <div id="wordcloud" class="small-12 columns">
  </div>

  <script type="text/javascript">

    var size = [500, 600];

    var fontSize = d3.scale.log().range([10, 30]);
    var words = <?= json_encode($words); ?>;

    d3.layout.cloud().size(size)
      .words(words)
      .fontSize(function (d) {
        return fontSize(+d.size);
      })
      .rotate(function () {
        return 0
      })
      .on("end", draw)
      .start();


    function draw(words) {
      d3.select("#wordcloud").append("svg")
        .attr("width", size[0])
        .attr("height", size[1])
        .append("g")
        .attr("transform", "translate(" + (size[0] / 2) + "," + (size[1] / 2) + ")")
        .selectAll("text")
        .data(words)
        .enter()
        .append("text")
        .style("font-size", function (d) {
          return d.size + "px";
        })
        .attr("text-anchor", "middle")
        .attr("transform", function (d) {
          return "translate(" + [d.x, d.y] + ")rotate(" + d.rotate + ")";
        })
        .attr("onclick", function (d) {
          return "{waiting_on(); location.href='" + d.link + "';}";
        })
        .text(function (d) {
          return d.text;
        })

    }


  </script>

</div>
