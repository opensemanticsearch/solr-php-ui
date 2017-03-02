var options, a;
jQuery(function(){
  options = { serviceUrl:'autocomplete.php', minChars:2 };
  a = $('#q').autocomplete(options);
});
