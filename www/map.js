var _map = null;
var markers = [];
var showMarkers = false;
var currentTripLogs;
var tripLatLngBounds;
var tripPathPolyLine = null;
var tripLatLngPath = [];
var markerId = 0;

var menuStyle, contextMenuOptions, contextMenu;

$(document).ready(function () {
    showMap(0, document.getElementById('trips-map-container'));
    loadTripList();

    $("#input-select-trips").change(function () {
        loadTripData($(this).find('option:selected').val());
    });

    $("#checkbox-show-markers").prop("checked", showMarkers);
    $("#checkbox-show-markers").click(function () {
        showMarkers = $(this).prop("checked");
        refreshMarkers();
        console.log("Selected : " + $("#input-select-trips").find('option:selected').val());
    });

});

function showMap(position, container) {
    var tempCenter = new google.maps.LatLng(50.45363, 4.707692);
    var mapOptions = {
        center: tempCenter,
        // center: new google.maps.LatLng(coords.coords.latitude, coords.coords.longitude),
        zoom: 15,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        scaleControl: true
    };

    _map = new google.maps.Map(container, mapOptions);
    initContextMenu();
    Infowindow = new google.maps.InfoWindow();
    // drawLine([latLng, otherPoint]);
}

function initContextMenu() {

    menuStyle = {
        menu: 'context_menu',
        menuSeparator: 'context_menu_separator'
    };

    contextMenuOptions  = {
        classNames: menuStyle,
        menuItems: [
            { label:'Hide', id:'menu_hide',
                className: 'context_menu_item', eventName:'menu_hide_clicked' },
            { label:'Show previous', id:'menu_show_previous',
                className: 'context_menu_item', eventName:'menu_show_previous_clicked' },
            { label:'Show next', id:'menu_show_next',
                className: 'context_menu_item', eventName:'menu_show_next_clicked' },
            { label:'Add Marker After', id:'menu_add_marker_after',
                className: 'context_menu_item', eventName:'menu_add_marker_after_clicked' }
        ],
        pixelOffset: new google.maps.Point(10, -5),
        zIndex: 5,
        classNames: {menu:'context_menu', menuSeparator:'context_menu_separator'}
    };

    contextMenu = new ContextMenu(_map, contextMenuOptions);

    google.maps.event.addListener(contextMenu, 'menu_item_selected',
        function(latLng, eventName, source){
            switch(eventName){
                case 'menu_hide_clicked':
                    // do something
                    console.log("clicked")
                    hideMarker(source.custom_sequence)
                    break;
                case 'menu_show_previous_clicked':
                    showMarker(source.custom_sequence - 1);
                    break;
                case 'menu_show_next_clicked':
                    showMarker(source.custom_sequence + 1);
                    break;
                case 'menu_add_marker_after_clicked':
                    addNewMarkerAtSequence(latLng, source);
                    break;
                default:
                    // freak out
                    break;
            }
        });

    mapMenuStyle = {
        menu: 'context_menu',
        menuSeparator: 'context_menu_separator'
    };

    mapContextMenuOptions  = {
        classNames: mapMenuStyle,
        menuItems: [
            { label:'Add Marker', id:'menu_add_marker',
                className: 'context_menu_item', eventName:'menu_add_marker_clicked' }

        ],
        pixelOffset: new google.maps.Point(10, -5),
        zIndex: 5,
        classNames: {menu:'context_menu', menuSeparator:'context_menu_separator'}
    };

    mapContextMenu = new ContextMenu(_map, mapContextMenuOptions);

    google.maps.event.addListener(mapContextMenu, 'menu_item_selected',
        function(latLng, eventName, source){
            switch(eventName){
                case 'menu_add_marker_clicked':
                    // do something
                    console.log("menu_add_marker_clicked")
                    addNewMarker(latLng);
                    break;
            }
        });

    google.maps.event.addListener(_map, 'rightclick', function(mouseEvent) {
        mapContextMenu.show(mouseEvent.latLng, _map);
    });
}

function loadTripList() {
    $.ajax({
        type: 'GET',
        headers: {
            "Access-Control-Allow-Origin": "*"
        },
        url: "../api/trip",
        success: function (result) {
            refreshTripCombo(result);
        },

        error: function (result) {
            switch (result.status) {
                case 404:
                   alert("Error happened while calling the server !");
            }
        },
    });
}

function loadTripData(tripId) {
    $.ajax({
        type: 'GET',
        headers: {
            "Access-Control-Allow-Origin": "*"
        },
        url: "../api/trip-log?trip_id="+tripId,
        success: function (result) {
            currentTripLogs = result;
            drawPath(result);
        },

        error: function (result) {
            switch (result.status) {
                case 404:
                    alert("Error happened while calling the server !");
            }
        },
    });
}

function drawPath(tripLogs) {
    markers = [];
    showMap(0, document.getElementById('trips-map-container'));
    tripLatLngPath = [];
    var lastLatitude = 0;
    var lastLongitude = 0;
    var distanceToLastCoordinates = 0;
    tripLatLngBounds = new google.maps.LatLngBounds();
    for (i = 0, j = 0; i < tripLogs.length; i++) {
        distanceToLastCoordinates = distance(lastLatitude, lastLongitude, tripLogs[i].latitude, tripLogs[i].longitude);
        if(distanceToLastCoordinates < 50) {
            console.log("Distance to last point "+distanceToLastCoordinates+", skipping")
        } else {
            console.log("Distance to last point "+distanceToLastCoordinates+", drawing")
            lastLatitude = tripLogs[i].latitude;
            lastLongitude = tripLogs[i].longitude;
            var latLng = new google.maps.LatLng(tripLogs[i].latitude, tripLogs[i].longitude);
            tripLatLngPath.push(latLng);
            tripLatLngBounds.extend(latLng);
            addTripLogMarker(tripLogs[i], j);
            if(showMarkers) {
                showMarker(j);
            }
            j++;
        }
    }
    drawLine();
    _map.fitBounds(tripLatLngBounds);
}

function markerClicked(marker, sequence) {
    toggleBounce(markers[sequence+ 1]);
}

function refreshMarkers() {
    for (i = 0, j = 0; i < markers.length; i++) {
        if(showMarkers) {
            showMarker(i)
        }  else {
            hideMarker(i);
        }
    }
}
function showMarker(sequence) {
    if(sequence >= 0 && sequence<markers.length) {
        markers[sequence].setMap(_map);
    }
}

function hideMarker(sequence) {
    if(sequence >= 0 && sequence<markers.length) {
        markers[sequence].setMap(null);
    }
}

function addNewMarkerAtSequence(position, marker) {
    var sequence = markers.findIndex(function (el) {
        return el.custom_marker_id === marker.custom_marker_id;
    })
    tripLatLngPath.splice(sequence + 1, 0, position);
    var marker = addMarker(position, "Sequence "+tripLatLngPath.length -1+"", sequence + 1);
    markers.splice(sequence + 1, 0, marker);
    drawLine();
}

function addNewMarker(position) {
    tripLatLngPath.push(position);
    var marker = addMarker(position, "Sequence "+tripLatLngPath.length -1+"", tripLatLngPath.length -1);
    markers.push(marker);
    drawLine();
}

function addMarker(position, title, sequence) {
    var marker = new google.maps.Marker({
        position: position,
        animation: google.maps.Animation.DROP,
        title: title,
        icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png',
        draggable: true,
        map: _map
    });
    marker.custom_marker_id = markerId++;
    marker.addListener('click', function() {
        markerClicked(marker, sequence);
    });

    google.maps.event.addListener(marker, 'rightclick', function(mouseEvent) {
        contextMenu.show(mouseEvent.latLng, marker);
    });

    google.maps.event.addListener(marker, 'dragend', function(evt) {
        var sequence = markers.findIndex(function (el) {
            return el.custom_marker_id === marker.custom_marker_id;
        })
        tripLatLngPath[sequence] = this.getPosition();
        drawLine();
        //updateLine();
    });
    return marker;
}

function addTripLogMarker(tripLog, sequence) {
    var position = new google.maps.LatLng(tripLog.latitude, tripLog.longitude);
    var title = 'Id: ' + tripLog.id + ', Sequence : ' + sequence + ', Date ' + new Date(tripLog);
    var marker = addMarker(position, title, sequence);
    markers.push(marker);
}

function updateLine() {
    tripPathPolyLine.setPath(tripLatLngPath);
}

function drawLine() {
    if(tripPathPolyLine != null) {
        tripPathPolyLine.setMap(null);
    }
    console.log("Trip path")
    tripLatLngPath.forEach(function (element, sequence ) {
        //console.log(element.lat() +', '+ element.lng()+', marker Id : '+markers[sequence].custom_marker_id);
    });

    tripPathPolyLine = new google.maps.Polyline({
        path: tripLatLngPath,
        strokeColor: "#6da1ff",
        strokeOpacity: 1.0,
        strokeWeight: 5,
        map: _map
    });
}

function refreshTripCombo(result) {
    for (i = 0; i < result.length; i++) {
        $("<option></option>").text(result[i].name).val(result[i].id)
            .appendTo($("#input-select-trips"));
    }
}


function toggleBounce(marker) {
    if (marker.getAnimation() !== null) {
        marker.setAnimation(null);
    } else {
        marker.setAnimation(google.maps.Animation.BOUNCE);
    }
}

function distance(lat1, lon1, lat2, lon2) {
    var radlat1 = Math.PI * lat1/180
    var radlat2 = Math.PI * lat2/180
    var theta = lon1-lon2
    var radtheta = Math.PI * theta/180
    var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
    dist = Math.acos(dist)
    dist = dist * 180/Math.PI
    dist = dist * 60 * 1.1515
    dist = dist * 1.609344 * 1000
    return dist
}


