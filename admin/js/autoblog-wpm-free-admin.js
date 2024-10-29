(function ($) {
  'use strict';
  
  /**
   * Shows spinner on form submit
   * @return {undefined}
   */
  var showSpinner = function () {
    $('form').submit(function () {
      $(this).find('.spinner').addClass('is-active');
    });
  };

  $(document).ready(function () {
    showSpinner();
  });



  // create help tabs
  var tabs = $('#tabs-titles li'); //grab tabs
  var contents = $('#tabs-contents li'); //grab contents

  tabs.bind('click',function(){
    contents.hide(); //hide all contents
    tabs.removeClass('current'); //remove 'current' classes
    $(contents[$(this).index()]).show(); //show tab content that matches tab title index
    $(this).addClass('current'); //add current class on clicked tab title
  });

})(jQuery);

function confirmDelete(urlRedirect){
  if(window.confirm('The file will be permanently removed. Are you sure? The published article, if any, will remain on your site.')) {
    window.location.href = urlRedirect;
  }
}

function redirectToUrl( urlRedirect ){
  window.location.href = urlRedirect;
}