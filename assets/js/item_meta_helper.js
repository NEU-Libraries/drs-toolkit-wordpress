jQuery(document).ready(function(){
  function assoc_checked(input, klass){
    // @TODO: this has clear signs of functionality creep beyond any semantic connection (PMJ)
    if (jQuery(input).is(":checked")){
      jQuery(klass).css("display","table-row");
    } else {
      jQuery(klass).css("display","none");
    }
  }

  assoc_checked("input[name='drstk_assoc']", ".assoc");
  assoc_checked("input[name='drstk_niec']", ".niec");
  assoc_checked("input[name='drstk_appears']", ".appears");
  assoc_checked("input[name='drstk_mirador']", ".mirador");
  assoc_checked("input[name='drstk_is_podcast']", ".drstk_podcast_options");

  jQuery("input[name='drstk_assoc']").on('change', function(){
    assoc_checked("input[name='drstk_assoc']", ".assoc");
  });
  jQuery("input[name='drstk_niec']").on('change', function(){
    assoc_checked("input[name='drstk_niec']", ".niec");
  });
  jQuery("input[name='drstk_appears']").on('change', function(){
    assoc_checked("input[name='drstk_appears']", ".appears");
  });
  jQuery("input[name='drstk_mirador']").on('change', function(){
    assoc_checked("input[name='drstk_mirador']", ".mirador");
  });

  jQuery("input[name='drstk_is_podcast']").on('change', function(){
      assoc_checked("input[name='drstk_is_podcast']", ".drstk_podcast_options");
    });

  
  
  jQuery("input[name='drstk_facets[]'], input[name='drstk_niec_metadata[]']").on('change', function(){
    if (jQuery(this).is(":checked")){
      jQuery(this).parents("td").next(".title").css("display","table-cell");
    } else {
      jQuery(this).parents("td").next(".title").css("display","none");
    }
  });

  jQuery("#facets_sortable").sortable();
  jQuery("#niec_facets_sortable").sortable();
  jQuery("#item_metadata_sortable").sortable();
  jQuery(".add-item-meta").on("click", function(e){
    e.preventDefault();
    jQuery("#item_metadata_sortable").append('<tr class="ui-sortable-handle"><td style="padding:0"><label><input type="checkbox" name="drstk_item_page_metadata[]"> <span class="dashicons dashicons-move"></span> <input type="text" /></label></td></tr>');
    jQuery("#item_metadata_sortable").sortable({ refresh: item_metadata_sortable });
    update_values();
  });

  function update_values(){
    jQuery("#item_metadata_sortable input[type='text']").on("change", function(e){
      val = jQuery(this).val();
      jQuery(this).siblings('input[type="checkbox"]').val(val);
      if (val != ""){
        jQuery(this).siblings('input[type="checkbox"]').prop("checked", true);
      } else {
        jQuery(this).siblings('input[type="checkbox"]').prop("checked", false);
      }
    });
  }
  
  // WP doesn't make it easy to add a class to settings page section headers,
  // so cheekily stuffing it in here
  
  jQuery('.settings_page_drstk_admin_menu h2').addClass('closed');
  
  jQuery('body').on('click', 'h2', function(e) {
     var sectionHeader = jQuery(e.target);
     sectionHeader.toggleClass('open closed');
     sectionHeader.next().toggle('slow');
  });

});
