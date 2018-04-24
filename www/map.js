var _map = null;

$(document).ready(function () {
    showMap(0, document.getElementById('trips-map-container'));
    loadTripList();

    $("#input-select-trips").change(function () {
        loadTripData($(this).find('option:selected').val());
    });
});



function showMap(position, container) {
    var tempCenter = new google.maps.LatLng(50.45363, 4.707692);
    var mapOptions = {
        center: tempCenter,
        // center: new google.maps.LatLng(coords.coords.latitude, coords.coords.longitude),
        zoom: 15,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    _map = new google.maps.Map(container, mapOptions);
    Infowindow = new google.maps.InfoWindow();
    // drawLine([latLng, otherPoint]);
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
    var latLng = new google.maps.LatLng(tripLogs[0].latitude, tripLogs[0].longitude);
    showMap(latLng, document.getElementById('trips-map-container'))
    var path = [];
    for (i = 0; i < tripLogs.length; i++) {
        var latLng = new google.maps.LatLng(tripLogs[i].latitude, tripLogs[i].longitude)
        addMArker(tripLogs[i], i)
        path.push(latLng);
    }
    drawLine(path);
}

function addMArker(tripLog, sequence) {
    var marker = new google.maps.Marker({
        position: new google.maps.LatLng(tripLog.latitude, tripLog.longitude),
        map: _map,
        animation: google.maps.Animation.DROP,
        title: 'Id: '+tripLog.id + ', Sequence : '+sequence+', Date '+new Date(tripLog),
        icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
    });
}

function drawLine(path) {

    var line = new google.maps.Polyline({
        path: path,
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

