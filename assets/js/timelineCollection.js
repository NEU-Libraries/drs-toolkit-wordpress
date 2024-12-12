/**
* Created by Abhi on 11/10/2016.
*/

jQuery(document).ready(function ($) {

    $("#search-and-facet").remove();
    $("#drs-selection").remove();
    $("#drs-facets").remove();

    $(".site-content .row").prepend('<div id="search-and-facet" class="col-md-2"><form id="check1" class="search"><input id="test1" type="text" placeholder="Search timeline ..."><button id="searchTimelineItems" class="fa fa-search" style="padding-right: 45px;"></button></form><br><br></div>');
    $("#search-and-facet").append("<div id='drs-facets' class='one_fourth hidden-phone hidden-xs hidden-sm'></div>");
    $("#content").prepend("<div id='drs-selection' class='col-md-10' style='padding-left: 7.5%;'></div>");

    var facets_recieved = facets_info_data_obj.data.response.facet_counts
    var atts = facets_info_data_obj.atts;
    var timeline_obj = facets_info_data_obj.timeline_obj;
    var sort = "score+desc%2C+system_create_dtsi+desc";
    var f = {};
    var q = '';
    var params1 = {q: q, f: f, sort: sort};
    var selectedItem = [];
    var post_id = page_id;

    drawFacetOnPageLoad(facets_recieved);

    function drawFacetOnPageLoad(data) {

        jQuery("#primary").removeClass("col-md-9");
        jQuery("#primary").addClass("col-md-8");

        jQuery("#secondary").removeClass("col-md-3");
        jQuery("#secondary").addClass("col-md-2");


        var facet_html = '';
        var facet_title = {creator_sim: "Creator", creation_year_sim: "Creation year", subject_sim: "Subject", drs_department_ssim: "Department", drs_degree_ssim: "Course Degree", drs_course_title_ssim: "Course Title"};

        facet_html = parse_facets(data, facet_title, facet_html);
        $("#drs-facets").html(facet_html);

        function parse_facets(data, object, facet_html) {
            $.each(object, function (facet, title) {
                var facet_name = title;
                var facet_values = '';
                if (Object.keys(data.facet_fields[facet]).length > 0) {
                    var this_facet, this_facet_name;
                    var facet_modal = facet_modal_vals = '';
                    var i = 1;
                    var facet_array = [];
                    $.each(data.facet_fields[facet], function (index, val_q) {
                        facet_array.push({v: index, k: val_q});
                    });
                    facet_array.sort(function (a, b) {
                        var sortBy = "fc_desc";
                        var sorts = sortBy.split("_");
                        var r1 = (sorts[1] === "desc" ? -1 : 1);
                        var type = (sorts[0] === "fc" ? 'k' : 'v');
                        if (a[type] > b[type]) {
                            return r1;
                        }
                        if (a[type] < b[type]) {
                            return r1 *= -1;
                        }
                        return 0;

                    });
                    $.each(facet_array, function (index, val_q) {
                        var this_facet_count = val_q.k;
                        this_facet_name = val_q.v;
                        if (this_facet_count != undefined) {
                            this_facet = "<a href='#' class='drs-facet-val row'><div class='three_fourth col-xs-8'>" + this_facet_name + "</div><div class='one_fourth col-xs-4 last'>" + this_facet_count + "</div></a>";
                            if (i <= 5) {
                                facet_values += this_facet;
                            }
                            facet_modal_vals += this_facet;
                        }
                        i++;
                    });
                    facet_modal = '<button type="button" class="themebutton btn btn-more" data-toggle="modal" data-target="#drs_modal_' + facet + '">More ' + facet_name + 's</button><div class="modal fade" id="drs_modal_' + facet + '"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title">All ' + facet_name + 's</h4></div><div class="modal-body">' + facet_modal_vals + '</div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div></div><!-- /.modal-content --></div><!-- /.modal-dialog --></div><!-- /.modal -->';
                    facet_html += "<div id='drs_" + facet + "' class='drs-facet'><div class='panel panel-default'><div class='panel-heading'><b class='drs-facet-name'>" + facet_name + "</b></div><div class='panel-body'>" + facet_values;
                    if (Object.keys(data.facet_fields[facet]).length > 5) {
                        facet_html += facet_modal;
                    }
                    facet_html += "</div></div></div>";
                }
            });
            return facet_html;
        }

        $("#drs-facets a").bind("click", function (e) {
            e.preventDefault();
            params1["page_no"] = 1;
            var facet = $(this).parents('.drs-facet').attr("id");
            if ($(this).parent().hasClass('modal-body')) {
                facet = $(this).parents('.modal').attr('id').substr(10);
                $(this).parents('.modal').modal('hide');
            } else {
                facet = facet.substr(4);
            }

            var facet_val = $(this).children(".drs-facet-val div:first-of-type").html();
            params1.f[facet] = facet_val;
            selectedItem.push(facet_val);
            if($("#timelineLoadingElement").length <= 0) {
                jQuery(".entry-header").append("<div id='timelineLoadingElement' class='themebutton btn btn-more'>Loading Timeline Items...</div>");
            }
            reloadTimeline(facets_info_data_obj, atts, params1, post_id);
            jQuery("#drs-selection").append("<a class='themebutton btn btn-more' href='#' data-type='f' data-facet='" + facet + "' data-val='" + facet_val + "'>" + titleize_1(facet) + " > " + facet_val + " <span class='fa fa-close'></span></a>");
            clickable_1();
        });
    }

    $('#check1').on('submit', function (e) {
        e.preventDefault();
    });

    $('#check1').on('keyup', '#test1', function (e) {
        if (e.keyCode == '13') {
            jQuery("#timelineErrorMsg").remove();
            search = $('#test1').val();
            $('#test1').val('');
            if ((search) && (search != '')) {
                params1["page_no"] = 1;
                params1["q"] = search;
                $("#drs-selection a[data-type='q']").remove();
                $("#drs-selection").append("<a class='themebutton btn btn-more' href='#' data-type='q' data-val='" + search + "'>" + search + " <span class='fa fa-close'></span></a>");
                reloadTimeline(facets_info_data_obj, atts, params1, post_id);
                if($("#timelineLoadingElement").length <= 0) {
                    jQuery(".entry-header").append("<div id='timelineLoadingElement' class='themebutton btn btn-more'>Loading Timeline Items...</div>");
                }
                clickable_1();
            }
        }
    });

    $('#searchTimelineItems').on('click', function (e) {
        e.preventDefault();
        jQuery("#timelineErrorMsg").remove();
        search = $('#test1').val();
        $('#test1').val('');
        if ((search) && (search != '')) {
            params1["page_no"] = 1;
            params1["q"] = search;
            $("#drs-selection a[data-type='q']").remove();
            $("#drs-selection").append("<a class='themebutton btn btn-more' href='#' data-type='q' data-val='" + search + "'>" + search + " <span class='fa fa-close'></span></a>");
            reloadTimeline(facets_info_data_obj, atts, params1, post_id);
            if($("#timelineLoadingElement").length <= 0) {
                jQuery(".entry-header").append("<div id='timelineLoadingElement' class='themebutton btn btn-more'>Loading Timeline Items...</div>");
            }
            clickable_1();
        }
    });

    function reloadTimeline(facets_info_data_obj, atts, params1, post_id) {
        $.ajax({
            type: 'POST',
            url: facets_info_data_obj.ajax_url,
            data: {
                _ajax_nonce: facets_info_data_obj.nonce,
                action: "reload_filtered_set_timeline",
                atts: atts,
                params: params1,
                reloadWhat: "timelineReload",
                post_id: post_id
            },
            success: function (data) {

                jQuery("#timelineErrorMsg").remove();

                if(data == "All_Pages_Loaded"){
                    jQuery("#timelineLoadingElement").remove();
                    console.log("All pages loaded ... Done .. No more Api calls");
                }
                else if(data == "No Result"){
                    $("#timelineLoadingElement").remove();
                    params1["q"] = '';
                    $("#drs-selection a[data-type='q']").remove();
                    jQuery("#check1").append("<div id='timelineErrorMsg'><span style=color:red;>No Results Found.</span></div>");
                }
                else {

                    var timelineDiv = jQuery(data).filter('#timeline-embed').empty()[0].outerHTML;

                    var timlineRes = jQuery(data).filter('#timeline-embed')[0].innerHTML;
                    var totalTimeline = '';


                    $('#timeline-embed').remove();

                    $(".entry-content").html(data);

                    var eventsList = getItemsFromJqueryArrayTimelineArray($('.timelineclass'));

                    var increments = $('#timeline-increments').data('increments');

                    var options = {scale_factor: increments};

                    var finalEventsListAfterCustomData = getTimelineCustomItems($('.custom-timeline'), eventsList);

                    var colorIds = getcolorIdsData($('#timeline-color-ids'));

                    for (var attrname in finalEventsListAfterCustomData['colorDict']) {
                        colorIds[attrname] = finalEventsListAfterCustomData['colorDict'][attrname];
                    }

                    var finalTimelineJson = {events: finalEventsListAfterCustomData['eventsList']};

                    window.timeline = new TL.Timeline('timeline-embed', finalTimelineJson, options);

                    itemBackgroundModifier($('.tl-timemarker-content-container'), colorIds);

                    reloadFacet(facets_info_data_obj, atts, params1);

                }
            }, error: function () {
                alert("failure");
                jQuery("#timelineLoadingElement").remove();
                jQuery("#timelineErrorMsg").remove();
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
                action: "reload_filtered_set_timeline",
                atts: atts,
                params: params1,
                reloadWhat: "facetReload"
            },
            success: function (data) {
                drawFacetOnPageLoad(data.response.facet_counts);
                jQuery("#timelineLoadingElement").remove();
            },
            error: function () {
                alert("failure");
                jQuery("#timelineLoadingElement").remove();
            }
        });
    }

    function clickable_1() {
        $("#drs-selection a").unbind("click");
        $("#drs-selection a").bind("click", function (e) {
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
            jQuery("#test1").val('');
            $(this).remove();
            params1["page_no"] = 1;
            if($("#timelineLoadingElement").length <= 0) {
                jQuery(".entry-header").append("<div id='timelineLoadingElement' class='themebutton btn btn-more'>Loading Timeline Items...</div>");
            }
            reloadTimeline(facets_info_data_obj, atts, params1, post_id);
            //clickable_1();
        });
    }

    function titleize_1(str) {
        str = str.replace("_tesim", "").replace("_sim", "").replace("_ssim", "").replace("drs_", "");
        str = str.replace("_", " ");
        str = str.replace(/\w\S*/g, function (txt) {
            return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        });
        return str;
    }
});
