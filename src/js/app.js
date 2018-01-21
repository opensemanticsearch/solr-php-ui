//
$(function () {
  /*
   * init foundation responsive framework
   */
  $(document).foundation();

  /*
   * Untoggle advanced search options on load.
   * we dont want to see animation on initial toggle off on load.
   */
  $(".searchoptions").hide();

});

/*
 * Display wait-indicator.
 */
function waiting_on() {
  $('#wait').show();
}
