/**
 * Created by Abhi on 11/10/2016.
 */

jQuery(document).ready(function($) {

    $("#search-and-facet").remove();
    $("#drs-selection").remove();
    $("#drs-facets").remove();

    $(".site-content .row").prepend('<div id="search-and-facet" class="col-md-2"><form id="check1" class="search"><input id="test1" type="text" placeholder="Search map items..."><button id="searchMapItems" class="fa fa-search" style="padding-right: 45px;"></button></form><br><br></div>');
    $("#search-and-facet").append("<div id='drs-facets' class='one_fourth hidden-phone hidden-xs hidden-sm'></div>");
    $("#content").prepend("<div id='drs-selection' class='col-md-10' style='padding-left: 7.5%;'></div>");

    var facets_recieved = facets_info_data_obj.data.response.facet_counts
    var atts = facets_info_data_obj.atts;
    var map_obj = facets_info_data_obj.map_obj;
    var sort = "score+desc%2C+system_create_dtsi+desc";
    var f = {};
    var q= '';
    var params1 = {q:q, f: f, sort:sort, page_no: 1};
    drawFacetOnPageLoad(facets_recieved);

    function drawFacetOnPageLoad(data){

        jQuery("#primary").removeClass("col-md-9");
        jQuery("#primary").addClass("col-md-8");

        jQuery("#secondary").removeClass("col-md-3");
        jQuery("#secondary").addClass("col-md-2");

        var facet_html = '';
        var facet_title = {creator_sim: "Creator",creation_year_sim: "Creation year",  subject_sim: "Subject", drs_department_ssim: "Department", drs_degree_ssim: "Course Degree", drs_course_title_ssim: "Course Title"};

        facet_html = parse_facets(data, facet_title, facet_html);
        $("#drs-facets").html(facet_html);

        function parse_facets(data, object, facet_html){
            $.each(object, function(facet, title){
                var facet_name = title;
                var facet_values = '';
                if (Object.keys(data.facet_fields[facet]).length > 0) {
                    var this_facet, this_facet_name;
                    var facet_modal = facet_modal_vals = '';
                    var i=1;
                    var facet_array = [];
                    $.each(data.facet_fields[facet],function(index, val_q){
                        facet_array.push({v:index, k:val_q});
                    });
                    facet_array.sort(function(a,b){
                        var sortBy = "fc_desc";
                        var sorts = sortBy.split("_");
                        var r1 = (sorts[1] === "desc" ? -1 : 1);
                        var type = (sorts[0] === "fc" ? 'k' : 'v');
                        if(a[type] > b[type]){ return r1; }
                        if(a[type] < b[type]){ return r1 *= -1; }
                        return 0;

                    });
                    $.each(facet_array, function(index, val_q) {
                        var this_facet_count = val_q.k;
                        this_facet_name = val_q.v;
                        if (this_facet_count != undefined) {
                            this_facet = "<a href='#' class='drs-facet-val row'><div class='three_fourth col-xs-8'>"+this_facet_name+"</div><div class='one_fourth col-xs-4 last'>"+this_facet_count+"</div></a>";
                            if (i <= 5){
                                facet_values += this_facet;
                            }
                            facet_modal_vals += this_facet;
                        }
                        i++;
                    });
                    facet_modal = '<button type="button" class="themebutton btn btn-more" data-toggle="modal" data-target="#drs_modal_'+facet+'">More '+facet_name+'s</button><div class="modal fade" id="drs_modal_'+facet+'"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title">All '+facet_name+'s</h4></div><div class="modal-body">'+facet_modal_vals+'</div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div></div><!-- /.modal-content --></div><!-- /.modal-dialog --></div><!-- /.modal -->';
                    facet_html += "<div id='drs_"+facet+"' class='drs-facet'><div class='panel panel-default'><div class='panel-heading'><b class='drs-facet-name'>" + facet_name + "</b></div><div class='panel-body'>"+facet_values;
                    if (Object.keys(data.facet_fields[facet]).length > 5){
                        facet_html += facet_modal;
                    }
                    facet_html += "</div></div></div>";
                }
            });
            return facet_html;
        }

        $( "#drs-facets a" ).bind( "click", function(e) {
            e.preventDefault();
            params1["page_no"] = 1;
            var facet = $(this).parents('.drs-facet').attr("id");
            if ($(this).parent().hasClass('modal-body')){
                facet = $(this).parents('.modal').attr('id').substr(10);
                $(this).parents('.modal').modal('hide');
            } else {
                facet = facet.substr(4);
            }
            jQuery("#mapErrorMsg").remove();
            var facet_val = $(this).children(".drs-facet-val div:first-of-type").html();
            params1.f[facet] = facet_val;
            if($("#mapLoadingElement").length <= 0){
                jQuery(".entry-header").append("<div id='mapLoadingElement' class='themebutton btn btn-more'>Loading Remaining Map Items...</div>");
            }
            reloadMap(facets_info_data_obj, atts, params1, post_id);
            jQuery("#drs-selection").append("<a class='themebutton btn btn-more' href='#' data-type='f' data-facet='"+facet+"' data-val='"+facet_val+"'>"+titleize_1(facet)+" > "+facet_val+" <span class='fa fa-close'></span></a>");
            clickable_1();
        });
    }

    $('#check1').on('submit', function(e) {
        e.preventDefault();
    });

    $('#check1').on('keyup', '#test1', function(e) {
        if (e.keyCode == '13') {
            jQuery("#mapErrorMsg").remove();
            search = $('#test1').val();
            $('#test1').val('');
            if ((search) && (search != '')){
                params1["page_no"] = 1;
                params1["q"] = search;
                $("#drs-selection a[data-type='q']").remove();
                if($("#mapLoadingElement").length <= 0){
                    jQuery(".entry-header").append("<div id='mapLoadingElement' class='themebutton btn btn-more'>Loading Remaining Map Items...</div>");
                }
                $("#drs-selection").append("<a class='themebutton btn btn-more' href='#' data-type='q' data-val='"+search+"'>"+search+" <span class='fa fa-close'></span></a>");
                reloadMap(facets_info_data_obj, atts, params1, post_id);
                clickable_1();
            }
        }
    });

    $('#searchMapItems').on('click', function (e) {
        e.preventDefault();
        jQuery("#mapErrorMsg").remove();
        search = $('#test1').val();
        $('#test1').val('');
        if ((search) && (search != '')){
            params1["page_no"] = 1;
            params1["q"] = search;
            $("#drs-selection a[data-type='q']").remove();
            if($("#mapLoadingElement").length <= 0){
                jQuery(".entry-header").append("<div id='mapLoadingElement' class='themebutton btn btn-more'>Loading Remaining Map Items...</div>");
            }
            $("#drs-selection").append("<a class='themebutton btn btn-more' href='#' data-type='q' data-val='"+search+"'>"+search+" <span class='fa fa-close'></span></a>");
            reloadMap(facets_info_data_obj, atts, params1, post_id);
            clickable_1();
        }
    });

    function reloadMap (facets_info_data_obj, atts, params1, post_id){
        console.log("Loading Remaining Map Items...Page no. "+params1["page_no"]);

        var page_no = params1["page_no"];

        $.ajax({
            type: 'POST',
            url: facets_info_data_obj.ajax_url,
            data: {
                _ajax_nonce: facets_info_data_obj.nonce,
                action: "reloadRemainingMap",
                atts: atts,
                params: params1,
                post_id: post_id,
            },
            success: function(data)
            {
                jQuery("#mapErrorMsg").remove();
                if(data == "All_Pages_Loaded"){
                    jQuery("#mapLoadingElement").remove();
                    console.log("All pages loaded ... Done .. No more Api calls");
                }
                else if(data == "No Result"){
                    $("#mapLoadingElement").remove();
                    params1["q"] = '';
                    $("#drs-selection a[data-type='q']").remove();
                    jQuery("#check1").append("<div id='mapErrorMsg'><span style=color:red;>No Results Found.</span></div>");
                }
                else if(jQuery(data).find(".coordinates").length > 0){

                    //to grab the map div
                    var mapDiv = jQuery(data).filter("#map").empty()[0].outerHTML;

                    //to grab the map div innerHTML i.e. coordinates
                    var resCoordinates = jQuery(data).filter("#map")[0].innerHTML;
                    var overallCoordiates = "";

                    if(page_no != 1){
                        //to grab existing map elements
                        var existingCoordinates = jQuery("#map").find(".coordinates");
                        var existingCustomCoordinates = jQuery("#map").find(".custom-coordinates");

                        var i = 0;
                        jQuery.each(existingCoordinates, function(){
                            overallCoordiates += existingCoordinates[i].outerHTML;
                            i = i+1;
                        });

                        var i = 0;
                        jQuery.each(existingCustomCoordinates, function(){
                            overallCoordiates += existingCustomCoordinates[i].outerHTML;
                            i = i+1;
                        });
                    }

                    overallCoordiates += resCoordinates;

                    jQuery('#map').remove();
                    jQuery(".entry-content").html(mapDiv);
                    jQuery("#map").html(overallCoordiates);

                    var home_url = map_obj.home_url;
                    var apiKey = getApiKey(jQuery('#map'));
                    var projectKey = getProjectKey(jQuery('#map'));
                    var colorHex = getColorHexArray($('#map'));
                    var colorGroups = getColorGroups(jQuery('#map'));
                    var colorDescriptions = getColorDescriptions(jQuery('#map'));
                    var items = getItemsFromJqueryArray(jQuery('.coordinates'));
                    var mymap = createMap('map');
                    addTileLayerToMap(mymap, apiKey, projectKey);
                    var markerCluster = addPopupsToItems(items, mymap, colorGroups, home_url,colorHex);
                    var customItems = getCustomItems(jQuery('.custom-coordinates'));
                    var markerCluster = addCustomItemsToMap(customItems, markerCluster, home_url);
                    fitToBounds(items, customItems, mymap);
                    addLegendToMap(colorDescriptions, mymap, home_url,colorHex);
                    if (isStoryModeEnabled(jQuery('#map'))) {
                        addStoryModeToMap(items, mymap, markerCluster, customItems);
                    }

                    //reload facet info once here.
                    if(page_no == 1){
                        reloadFacet(facets_info_data_obj, atts, params1);
                    }

                    page_no = page_no+1;
                    params1["page_no"] = page_no;

                    reloadMap(facets_info_data_obj, atts, params1, post_id);

                }
                else{
                    $("#mapLoadingElement").remove();
                    jQuery("#check1").append("<div id='mapErrorMsg'><span style=color:red;>No Coordinates Found for Selected Item.</span></div>");
                }
            }, error: function()
            {
                alert("failure");
                jQuery("#mapLoadingElement").remove();
                jQuery("#mapErrorMsg").remove();
                $("#drs-selection").empty();
            }
        });
    }

    function reloadFacet(facets_info_data_obj, atts, params1) {
        jQuery.ajax({
            type: 'POST',
            url: facets_info_data_obj.ajax_url,
            data: {
                _ajax_nonce: facets_info_data_obj.nonce,
                action: "reload_filtered_set",
                atts: atts,
                params: params1,
                reloadWhat: "facetReload"
            },
            success: function(data) {
                drawFacetOnPageLoad(data.response.facet_counts);
            },
            error: function(){
                alert("failure");
                jQuery("#mapLoadingElement").remove();
            }
        });
    }

    function clickable_1(){
        $("#drs-selection a").unbind("click");
        $("#drs-selection a").bind("click", function(e){
            e.preventDefault();
            var type = $(this).data("type");
            if (type == 'f') {
                var facet = $(this).data("facet");
                delete params1.f[facet];
            } else if (type == 'q') {
                params1[type] = '';
            } else {
                params1[type] = '';
            }
            $(this).remove();
            params1["page_no"] = 1;
            if($("#mapLoadingElement").length <= 0){
                jQuery(".entry-header").append("<div id='mapLoadingElement' class='themebutton btn btn-more'>Loading Remaining Map Items...</div>");
            }
            reloadMap(facets_info_data_obj, atts, params1, post_id);
            //clickable_1();
        });
    }

    function titleize_1(str){
        str = str.replace("_tesim","").replace("_sim","").replace("_ssim","").replace("drs_","");
        str = str.replace("_", " ");
        str = str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
        return str;
    }
});
