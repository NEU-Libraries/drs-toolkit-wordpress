jQuery(document).ready(function($) {
  $(".drstk-caption").each(function(){
    if($(this).data('caption-align')){
      $(this).css("text-align", $(this).data('caption-align'));
    }
    if($(this).data('caption-position') == 'hover'){
      $(this).parents(".drs-item").addClass("hover");
    }
  });
  $("img.drs-item-img").each(function(){
    if ($(this).data('align')){
      $(this).parent('a').css("text-align", $(this).data('align'));
    }
    if ($(this).data('float')){
      $(this).parents('.drs-item').css("float",$(this).data('float'));
      if ($(this).data('float') == 'left'){
        $(this).parents('.drs-item').css("padding", "30px 30px 30px 0");
      } else if ($(this).data('float') == 'right'){
        $(this).parents('.drs-item').css("padding", "30px 0 30px 30px");
      }
    }
    if ($(this).data('zoom') == 'on'){
      if($(this).attr('data-zoom-position') == 'inner'){
        $(this).elevateZoom({
          zoomType	: "inner",
          cursor: "crosshair"
        });
      } else if ($.isNumeric($(this).attr('data-zoom-position'))){
        var position = parseInt($(this).attr('data-zoom-position'));
        $(this).elevateZoom({ zoomWindowPosition: position});
      } else {
        $(this).elevateZoom();
      }
    }
    $(this).on("load", function(){
      if ($(this).parents(".drs-item").hasClass("hover")){
        width = $(this).innerWidth() - 30;
        height = $(this).innerHeight() - 30;
        $(this).siblings(".drstk-caption").width(width);
        $(this).siblings(".drstk-caption").height(height);
      }
    });
  });
  $(".hidden").each(function(){
    $(this).appendTo("body");
  });
});//end doc ready
