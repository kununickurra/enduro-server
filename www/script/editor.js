var editor = null;

$(document).ready(function () {

    editor = new ItineraryEditor(document.getElementById("editor-map-container"));
    editor.show(new google.maps.LatLng(50.45363, 4.707692));

    $("#button-save-itinerary").click(function () {
        var itineraryData = new Object();
        itineraryData.name = $("#text-itinerary-name").val();
        var itineraryAnchors = editor.getItinerary();
        var path = [];
        for (var i = 0; i<itineraryAnchors.length; i++) {
            path.push({
                        latitude : itineraryAnchors[i].lat(),
                        longitude : itineraryAnchors[i].lng()
                      }
            )
        }
        itineraryData.path = path;
        uploadItinerary(itineraryData);
    })

});

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