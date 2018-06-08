var editor = null;

$(document).ready(function () {
    editor = new ItineraryEditor(document.getElementById("editor-map-container"));
    editor.show(new google.maps.LatLng(50.45363, 4.707692));

    $("#button-save-itinerary").click(function () {
        var path = editor.getItinerary();
        for(var i = 0; i<path.length; i++) {

        }
    })

});


function uploadItinerary() {
    $.ajax({
        type: 'POST',
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