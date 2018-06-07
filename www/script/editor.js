// Itinerary editor functions
var ItineraryEditor = (function () {

    function ItineraryEditor(container) {
        // Counter for new Markers
        this.markerId = 0;

        this.showMarkers = false;
        this.map = null;
        this.infowindow = null;
        this.mapContextMenu = null;

        this.mapContainer = container;

        //Current Way points structure.
        this.tripLatLngPath = [];
        this.markers = [];

        this.tripLatLngBounds = null;
        this.tripPathPolyLine = null;

        this.consumeMarkerId = function (marker) {
            return this.markerId++;
        }

        this.insertMarker = function (position, sequence) {
            var marker = this.createMarker(position, "New Marker");
            if (sequence >= this.markers.length) {
                this.tripLatLngPath.push(marker.getPosition());
                this.markers.push(marker);
            } else {
                this.tripLatLngPath.splice(sequence + 1, 0, marker.getPosition());
                this.markers.splice(sequence + 1, 0, marker);
            }
            this.drawLine();
        }

        this.deleteMarker = function (marker) {
            marker.setMap(null);
            var sequence = this.markers.findIndex(function (el) {
                return el.custom_marker_id === marker.custom_marker_id;
            })
            this.tripLatLngPath.splice(sequence, 1);
            this.markers.splice(sequence, 1);
            this.drawLine();
        }

        this.addNewMarker = function (position) {
            this.insertMarker(position, this.markers.length);
        }

        this.addNewMarkerAfter = function (position, marker) {
            var sequence = this.markers.findIndex(function (el) {
                return el.custom_marker_id === marker.custom_marker_id;
            })
            this.insertMarker(position, sequence);
        }

        this.createMarker = function (position, title) {
            var self = this;
            var marker = new google.maps.Marker({
                position: position,
                animation: google.maps.Animation.DROP,
                title: title,
                icon: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png',
                draggable: true,
                map: this.map
            });
            marker.custom_marker_id = this.markerId++;

            google.maps.event.addListener(marker, 'dragend', function (evt) {
                var sequence = self.markers.findIndex(function (el) {
                    return el.custom_marker_id === marker.custom_marker_id;
                })
                self.tripLatLngPath[sequence] = this.getPosition();
                self.drawLine();
            });

            var markerContextMenu = createMarkerContextMenu(this.map, marker);
            google.maps.event.addListener(markerContextMenu, 'menu_item_selected',
                function (latLng, eventName, source) {
                    switch (eventName) {
                        case 'menu_add_marker_after_clicked':
                            self.addNewMarkerAfter(latLng, source);
                            break;
                        case 'menu_delete_marker_clicked':
                            self.deleteMarker(source);
                            break;
                        default:
                            // freak out
                            break;
                    }
                });
            return marker;
        }

        this.drawLine = function () {
            var self = this;
            if (this.tripPathPolyLine != null) {
                this.tripPathPolyLine.setPath(this.tripLatLngPath)
                this.tripLatLngPath.forEach(function (element, sequence) {
                    console.log(element.lat() + ', ' + element.lng() + ', marker Id : ' + self.markers[sequence].custom_marker_id);
                });
            } else {
                this.tripPathPolyLine = new google.maps.Polyline({
                    path: this.tripLatLngPath,
                    strokeColor: "#6da1ff",
                    strokeOpacity: 1.0,
                    strokeWeight: 5,
                    map: this.map
                });
            }

        }

    }

    ItineraryEditor.prototype.testAccess = function (position) {
        console.log(consumeMarkerId.call(ItineraryEditor));
    }

    // Public prototype function
    ItineraryEditor.prototype.show = function (position) {
        var self = this;
        var mapOptions = {
            center: position,
            zoom: 15,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            scaleControl: true
        };

        this.map = new google.maps.Map(this.mapContainer, mapOptions);
        this.infowindow = new google.maps.InfoWindow();
        this.mapContextMenu = createMapContextMenu(this.map);

        // Add marker Listener.
        google.maps.event.addListener(this.mapContextMenu, 'menu_item_selected',
            function (latLng, eventName, source) {
                switch (eventName) {
                    case 'menu_add_marker_clicked':
                        self.addNewMarker(latLng);
                        break;
                }
            });
    }

    return ItineraryEditor;

})();