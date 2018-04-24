<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <style>
        #map-container {
            height: 600px;
            width: 100%;
            /*padding: 0;*/
            /*position : absolute !important;*/
            /*top : 120px !important;*/
            /*right : 0;*/
            /*bottom : 50px !important;*/
            /*left : 0 !important;*/
        }
        #trips-map-container {
            height: 600px;
            width: 100%;
            /*padding: 0;*/
            /*position : absolute !important;*/
            /*top : 120px !important;*/
            /*right : 0;*/
            /*bottom : 50px !important;*/
            /*left : 0 !important;*/
        }
    </style>

    <title>Title</title>
    <div data-role="">
        <select id="input-select-trips" placeholder="Select a trip">
        </select>
        <div id="trips-map-container" data-role="content"></div>
    </div>
</head>
<body>
    <script
            src="http://code.jquery.com/jquery-3.3.1.js"
            integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60="
            crossorigin="anonymous"></script>

<!--    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBLWf2mw5gIizjqwsOK35YoK7CRZtA1e80&libraries=places"></script>-->
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyBLWf2mw5gIizjqwsOK35YoK7CRZtA1e80&libraries=geometry"></script>

    <script src="map.js"></script>
</body>
</html>