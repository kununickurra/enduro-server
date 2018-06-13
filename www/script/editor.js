var editor = null;

$(document).ready(function () {

    editor = new ItineraryEditor(document.getElementById("editor-map-container"));
    editor.show(new google.maps.LatLng(50.45363, 4.707692));

    loadItineraryList();

    $("#button-save-itinerary").click(function () {
        var itineraryData = convertToDto(editor.getItinerary());
        itineraryData.name = $("#text-itinerary-name").val();
        uploadItinerary(itineraryData);
    })


    $("#button-update-itinerary").click(function () {
        var itineraryId = $("#input-select-itinerary").find('option:selected').val();
        if(!isNaN(itineraryId)) {
            var itineraryData = convertToDto(editor.getItinerary());
            itineraryData.name = $("#text-itinerary-name").val();
            itineraryData.id = itineraryId;
            updateItinerary(itineraryData, loadItineraryList);
        }
    });

    $("#input-select-itinerary").change(function () {
        var itineraryId = $(this).find('option:selected').val();
        if(itineraryId == "") {
            editor.clearPath();
        } else {
            var itineraryName = $(this).find('option:selected').text();
            loadItineraryData(itineraryId);
            $("#text-itinerary-name").val(itineraryName);
        }
    });


});


function refreshEditor(itineraryData) {
    var path = [];
    for (var i = 0; i<itineraryData.length; i++) {
        path.push(new google.maps.LatLng(
            itineraryData[i].latitude, itineraryData[i].longitude));
    }
    editor.initPath(path);
}


function convertToDto(itineraryAnchors) {
    var itineraryData = new Object();
    var path = [];
    for (var i = 0; i<itineraryAnchors.length; i++) {
        path.push({
                latitude : itineraryAnchors[i].lat(),
                longitude : itineraryAnchors[i].lng()
            }
        )
    }
    itineraryData.path = path;
    return itineraryData;
}

function uploadItinerary(data) {
    $.ajax({
        type: 'POST',
        data: JSON.stringify(data),
        headers: {
            "Access-Control-Allow-Origin": "*"
        },
        url: "../api/itinerary",

        success: function (result) {
            alert("Save successful !");
        },

        error: function (result) {
            switch (result.status) {
                case 404:
                    alert("Error happened while calling the server !");
            }
        },
    });
}

function updateItinerary(data, onSuccess) {
    $.ajax({
        type: 'PUT',
        data: JSON.stringify(data),
        headers: {
            "Access-Control-Allow-Origin": "*"
        },
        contentType: 'application/json; charset=utf-8',
        dataType: 'text',
        url: "../api/itinerary/"+data.id,

        success: function (result) {
            onSuccess(result);
        },

        error: function (result) {
            switch (result.status) {
                case 200:
                    alert("Error happened while calling the server !");
            }
        },
    });
}

function loadItineraryList() {
    $.ajax({
        type: 'GET',
        headers: {
            "Access-Control-Allow-Origin": "*"
        },
        url: "../api/itinerary",
        success: function (result) {
            refreshItineraryCombo(result);
        },

        error: function (result) {
            switch (result.status) {
                case 404:
                    alert("Error happened while calling the server !");
            }
        },
    });
}

function loadItineraryData(itineraryId) {
    $.ajax({
        type: 'GET',
        headers: {
            "Access-Control-Allow-Origin": "*"
        },
        url: "../api/anchor?itinerary_id="+itineraryId,
        success: function (result) {
            refreshEditor(result);
        },
        error: function (result) {
            switch (result.status) {
                case 404:
                    alert("Error happened while calling the server !");
            }
        },
    });
}

function refreshItineraryCombo(result) {
    var itineraryId = $("#input-select-itinerary").find('option:selected').val();
    $("#input-select-itinerary").empty();

    $("<option></option>").text("").val("").appendTo($("#input-select-itinerary"));
    for (i = 0; i < result.length; i++) {
        $("<option></option>").text(result[i].name).val(result[i].id)
            .appendTo($("#input-select-itinerary"));
    }
    $("#input-select-itinerary").val(itineraryId);
}

