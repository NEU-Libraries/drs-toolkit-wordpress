var post_id = "";
jQuery(document).ready(function($) {
    var home_url = map_obj.home_url;
    post_id = map_obj.post_id;
    var collectionSet = map_obj.collectionSet;

    var apiKey = getApiKey($('#map'));
    var projectKey = getProjectKey($('#map'));
    var f = {};
    var q= '';
    var params = {q:q, f: f, page_no: 2};

    var colorGroups = getColorGroups($('#map'));
    var colorDescriptions = getColorDescriptions($('#map'));
    var colorHex = getColorHexArray($('#map'));
    var items = getItemsFromJqueryArray($('.coordinates'));

    var mymap = createMap('map');
    addTileLayerToMap(mymap, apiKey, projectKey);

    var markerCluster = addPopupsToItems(items, mymap, colorGroups, home_url,colorHex);

    var customItems = getCustomItems($('.custom-coordinates'));

    var markerCluster = addCustomItemsToMap(customItems, markerCluster, home_url,colorHex);

    fitToBounds(items, customItems, mymap);

    addLegendToMap(colorDescriptions, mymap, home_url,colorHex);

    if (isStoryModeEnabled($('#map'))) {
        addStoryModeToMap(items, mymap, markerCluster, customItems);
    }

    if(collectionSet == "checked"){
    //do this only for collection_id (PUT CHECK)
    reloadRemainingMap(map_obj, params, post_id, 2);
    jQuery(".entry-header").append("<div id='mapLoadingElement' class='themebutton btn btn-more'>Loading Remaining Map Items...</div>");
    }
});

function reloadRemainingMap(map_obj, params, post_id){

    var page_no = params["page_no"];
    jQuery.ajax({
        type: 'POST',
        url: map_obj.ajax_url,
        data: {
            _ajax_nonce: map_obj.nonce,
            action: "reloadRemainingMap",
            params: params,
            post_id: post_id
        },
        success: function (data) {
            page_no = page_no+1;
            params["page_no"] = page_no;
            if(data == "All_Pages_Loaded"){
                jQuery("#mapLoadingElement").remove();
                console.log("All pages loaded ... Done .. No more Api calls");
            }else{
                //WILL HAVE TO INCLUDE FOR CUSTOM COORDINATES
                //to grab the map div
                var mapDiv = jQuery(data).filter("#map").empty()[0].outerHTML;

                //to grab the map div innerHTML i.e. coordinates
                var resCoordinates = jQuery(data).filter("#map")[0].innerHTML;

                //to grab existing map elements
                var existingCoordinates = jQuery("#map").find(".coordinates");
                var existingCustomCoordinates = jQuery("#map").find(".custom-coordinates");
                var overallCoordiates = "";

                var i = 0;
                jQuery.each(existingCoordinates, function(){
                    overallCoordiates += existingCoordinates[i].outerHTML;
                    i = i+1;
                });

                overallCoordiates += resCoordinates;

                var i = 0;
                jQuery.each(existingCustomCoordinates, function(){
                    overallCoordiates += existingCustomCoordinates[i].outerHTML;
                    i = i+1;
                });


                jQuery('#map').remove();
                jQuery(".entry-content").html(mapDiv);
                jQuery("#map").html(overallCoordiates);

                var home_url = map_obj.home_url;
                var apiKey = getApiKey(jQuery('#map'));
                var projectKey = getProjectKey(jQuery('#map'));
                var colorHex = getColorHexArray(jQuery('#map'));
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
                reloadRemainingMap(map_obj, params, post_id);
            }
        }
    });
}

function getApiKey(jqSelector) {
    return jqSelector.data('map_api_key');
}

function getProjectKey(jqSelector) {
    return jqSelector.data('map_project_key');
}

var colorDescriptions = {};
function getColorHexArray(jqSelector){
    var colorHexArray ={}
    jQuery.each(jqSelector.data(),function(key,value){
        var strlength = key.length;
        var endMatch = key.substring(strlength-14,strlength);
        var match = endMatch.match(/color_hex/i);
        if(match){
            var key = key.substring(0,strlength-10)
            colorHexArray[key]=value;
        }
    });
    return colorHexArray
}
function getColorDescriptions(jqSelector) {
    var colorDescriptions = {}
    if (jqSelector.data('red_legend_desc')) {
        colorDescriptions.red = jqSelector.data('red_legend_desc');
    }
    if (jqSelector.data('blue_legend_desc')) {
        colorDescriptions.blue = jqSelector.data('blue_legend_desc');
    }
    if (jqSelector.data('green_legend_desc')) {
        colorDescriptions.green = jqSelector.data('green_legend_desc');
    }
    if (jqSelector.data('yellow_legend_desc')) {
        colorDescriptions.yellow = jqSelector.data('yellow_legend_desc');
    }
    if (jqSelector.data('orange_legend_desc')) {
        colorDescriptions.orange = jqSelector.data('orange_legend_desc');
    }
    jQuery.each(jqSelector.data(),function(key,value){
        var strlength = key.length;
        var colLegendVal=[];
        var endMatch = key.substring(strlength-14,strlength);
        var match = endMatch.match(/color_desc_id/i);
        if(match)
        {   var colLegendValTemp=[];
            colLegendValTemp = key.substring(0,strlength-14);
            colLegendValTemp = colLegendValTemp.split('_');
            jQuery.each(colLegendValTemp,function(key,val){
                colLegendVal.push(val.charAt(0).toUpperCase()+val.substring(1));
            });
            colLegendVal = colLegendVal.join(' ');
            colorDescriptions[key]=colLegendVal;
        }
    });
    return colorDescriptions;
}

function getColorGroups(jqSelector) {
    var colorGroups = {};
    if (jqSelector.data('red')) {
        colorGroups.red = jqSelector.data('red');
    }
    if (jqSelector.data('blue')) {
        colorGroups.blue = jqSelector.data('blue');
    }
    if (jqSelector.data('green')) {
        colorGroups.green = jqSelector.data('green');
    }
    if (jqSelector.data('yellow')) {
        colorGroups.yellow = jqSelector.data('yellow');
    }
    if (jqSelector.data('orange')) {
        colorGroups.orange = jqSelector.data('orange');
    }
    jQuery.each(jqSelector.data(),function(key,value){
        var strlength = key.length;
        var endMatch = key.substring(strlength-14,strlength);
        var match = endMatch.match(/color_desc_id/i);
        if(match)
        {
            colorGroups[key]=value;
        }
    });
    return colorGroups;
}

function addLegendToMap(colorDescriptions, mymap, home_url,colorHex) {

    var isLegendRequired = false;
    var legendHtml = '<table style="margin-top: 0px; margin-bottom: 0px">';
    jQuery.each(colorDescriptions, function(key, value) {
        if (value != 'undefined') {
            isLegendRequired = true;
            var strlength = key.length;
            var colLegendKey = key.substring(0,strlength-14);
            jQuery.each(colorHex,function(colkey,hexval){
                if(colLegendKey.toLowerCase()==colkey.toLowerCase()){
                    iconUrl = "http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|"+hexval;
                    //legendHtml += '<tr><td><img src="'+home_url+'wp-content/plugins/drs-tk/assets/js/leaflet/images/marker-'+ key + '-icon.png" style="height:20px"> </td><td>' + value + '</td></tr>';
                    legendHtml += '<tr><td><img src='+ iconUrl+' style="height:20px"> </td><td>' + value + '</td></tr>';
                }
            });
              }
    });

    if (!isLegendRequired) {
        return;
    }

    var box = L.control.messagebox().addTo(mymap);

    legendHtml += '</table>';

    box.show(legendHtml);
}

function getCordinatesFromString(input) {
    if (!input) {
        return null;
    }
    return [
        input.split(',')[0].trim(),
        input.split(',')[1].trim()
    ];
}

function getItemsFromJqueryArray(jqArray) {
    var items = [];
    jqArray.each(function(index) {
        if (dataExists(jqArray[index])) {
            items.push({
                pid: jQuery(jqArray[index]).data('pid'),
                title: jQuery(jqArray[index]).data('title'),
                coordinates: getCordinatesFromString(jQuery(jqArray[index]).data('coordinates')),
                url: jQuery(jqArray[index]).data('url')
            });
        }
        if (metaDataExists(jqArray[index])) {
            items[index].metadata = jQuery(jqArray[index]).data('metadata');
        }
        if (mediaContentExists(jqArray[index])) {
            items[index].media_content = jQuery(jqArray[index]).data('media-content');
        }
    });

    return items;
}

function dataExists(input) {
    return pidExists(input) && titleExists(input) && coordinatesExists(input) && urlExists(input);
}

function pidExists(input) {
    return (jQuery(input).data('pid')) ? true : false;
}
function titleExists(input) {
    return (jQuery(input).data('title')) ? true : false;
}
function coordinatesExists(input) {
    return (jQuery(input).data('coordinates')) ? true : false;
}
function metaDataExists(input) {
    return (jQuery(input).data('metadata')) ? true : false;
}
function mediaContentExists(input) {
    return (jQuery(input).data('media-content')) ? true : false;
}
function urlExists(input) {
    return (jQuery(input).data('url')) ? true : false;
}

function createMap(mapID) {
    if (!mapID) {
        return null;
    }
    return L.map(mapID);
}

function fitToBounds(items, customItems, map) {
    var bounds = [];
    jQuery.each(items, function(index, item) {
        bounds.push(item.coordinates);
    });
    jQuery.each(customItems, function(index, item) {
        bounds.push(item.coordinates);
    });
    if (map) {
        map.fitBounds(bounds);
    }

    var isSameCoordinates = true;
    for(var i = 1; i < bounds.length; i++)
    {
        if(bounds[i] !== bounds[0]) {
            isSameCoordinates = false;
        }
    }

    if (isSameCoordinates && map) {
        map.setZoom(13);
    }
    return bounds;
}

function addPopupsToItems(items, map, colorGroups, home_url,colorHex) {
    var markers = L.markerClusterGroup();
    var markerArray = [];

    jQuery.each(items, function(index, item) {

        var icon = L.icon({
            iconUrl: 'http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|0080ff',
            iconRetinalUrl:'',
            iconSize: [29, 41],
            iconAnchor: [14, 41],
            popupAnchor: [0, -41],
            shadowUrl: '',
            shadowRetinaUrl: '',
            shadowSize: [41, 41],
            shadowAnchor: [13, 41]
        });


        jQuery.each(colorGroups,function(key,value){
            if(value.includes(item.pid))
            { var strlength = key.length;
                var colorLegendId = key.substring(0,strlength-14);
                jQuery.each(colorHex,function(hexItem,hexVal){
                    var match = colorLegendId.toLowerCase().match(hexItem.toLowerCase());
                    if(match)
                    {
                        var iconUrlHexValue ="http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|"+hexVal;
                        icon = L.icon({
                            iconUrl: iconUrlHexValue,
                            iconRetinalUrl: '',
                            iconSize: [29, 41],
                            iconAnchor: [14, 41],
                            popupAnchor: [0, -41],
                            shadowUrl: '',
                            shadowRetinaUrl:'',
                            shadowSize: [41, 41],
                            shadowAnchor: [13, 41]
                        });
                    }
                });
            }
        });


        var marker = L.marker(
            new L.LatLng(item.coordinates[0], item.coordinates[1]),
            {
                title: item.title,
                icon: icon
            });
        var url = item.url;
        if (url.indexOf("hdl.handle") > -1){
          url = home_url + 'item/' + item.pid;
        }
        var popupContent = "<a href='" + url + "' target='_blank'>" + item.title + "</a><br/>";

        if (item.metadata) {
            popupContent += item.metadata
        }
        if (item.media_content){
            popupContent = item.media_content + popupContent
        }

        marker.bindPopup(popupContent);
        markers.addLayer(marker);
        markerArray.push(marker)
    });
    map.addLayer(markers);

    return {
        cluster: markers,
        markerArray: markerArray
    };
}

function isStoryModeEnabled(jqSelector) {
    return (jqSelector.data('story') === 'yes');
}

function addStoryModeToMap(drsitems, map, markerCluster, customItems) {
    var items = drsitems.concat(customItems);

    var itemIndex = 0;

    L.easyButton('fa-play', function(btn, map){
        jQuery.each(items, function(key) {
            if (key === itemIndex) {
                map.setView(items[key].coordinates);
                markerCluster.cluster.zoomToShowLayer(markerCluster.markerArray[key], function() {
                    markerCluster.markerArray[key].openPopup();
                });

                itemIndex++;
                if (itemIndex === items.length) {
                    itemIndex = 0;
                }

                return false;
            }
        });
    }, 'Play').addTo(map);

    L.easyButton('fa-arrows-alt', function(btn, map){
        itemIndex = 0;
        fitToBounds(drsitems, customItems, map);
    }, 'Reset').addTo(map);
}

function addTileLayerToMap(map, apiKey, projectKey) {

    var baseLayers = {};

    var openStreetMap = L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
        maxZoom: 18
    });
    baseLayers['OpenStreetMap'] = openStreetMap;

    var mapnikLayer = L.tileLayer('http://{s}.www.toolserver.org/tiles/bw-mapnik/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
        maxZoom: 18
    });
    baseLayers['Mapnik'] = mapnikLayer;

    if (apiKey !=='' && projectKey !== '') {
        var mapBox = L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
            attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>',
            maxZoom: 18,
            id: projectKey,
            accessToken: apiKey
        });

        baseLayers['Mapbox'] = mapBox;
        mapBox.addTo(map);
    } else {
        openStreetMap.addTo(map);
    }


    L.control.layers(baseLayers).addTo(map);

}

function getCustomItems(jqArray) {
    var items = [];
    jqArray.each(function(index) {
        items.push({
            url: jQuery(jqArray[index]).data('url'),
            title: jQuery(jqArray[index]).data('title'),
            coordinates: getCordinatesFromString(jQuery(jqArray[index]).data('coordinates')),
            description: jQuery(jqArray[index]).data('description'),
            colorgroup: jQuery(jqArray[index]).data('colorgroup')
        });
    });

    return items;
}

function addCustomItemsToMap(items, markerCluster, home_url,colorHex) {

    jQuery.each(items, function(index, item) {

        var icon = L.icon({
            iconUrl: home_url + 'wp-content/plugins/drs-tk/assets/js/leaflet/images/marker-blue-icon.png',
            iconRetinalUrl: home_url + 'wp-content/plugins/drs-tk/assets/js/leaflet/images/marker-blue-icon-2x.png',
            iconSize: [29, 41],
            iconAnchor: [14, 41],
            popupAnchor: [0, -41],
            shadowUrl: home_url + 'wp-content/plugins/drs-tk/assets/js/leaflet/images/marker-shadow.png',
            shadowRetinaUrl: home_url + 'wp-content/plugins/drs-tk/assets/js/leaflet/images/marker-shadow.png',
            shadowSize: [41, 41],
            shadowAnchor: [13, 41]
        });
        jQuery.each(colorGroups,function(key,value){
            if(value.includes(item.pid))
            { var strlength = key.length;
                var colorLegendId = key.substring(0,strlength-14);
                jQuery.each(colorHex,function(hexItem,hexVal){
                    var match = colorLegendId.toLowerCase().match(hexItem.toLowerCase());
                    if(match)
                    {  var iconUrlHexValue ="http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|"+hexVal;
                        icon = L.icon({
                            iconUrl: iconUrlHexValue,
                            iconRetinalUrl: '',
                            iconSize: [29, 41],
                            iconAnchor: [14, 41],
                            popupAnchor: [0, -41],
                            shadowUrl: '',
                            shadowRetinaUrl:'',
                            shadowSize: [41, 41],
                            shadowAnchor: [13, 41]
                        });
                    }
                });
            }
        });

        var marker = L.marker(
            new L.LatLng(item.coordinates[0], item.coordinates[1]),
            {
                title: item.title,
                icon: icon
            });
        var url = item.url;
        if (url.indexOf('http') == -1) {
            url = "http://" + item.url;
        }
        var popupContent = "<a href='" + url + "' target='_blank'>" + item.title + "</a><br/>";

        popupContent += item.description;

        marker.bindPopup(popupContent);
        markerCluster.cluster.addLayer(marker);
        markerCluster.markerArray.push(marker);
    });

    return markerCluster;
}
