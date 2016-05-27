function geocodeService(address) {
    url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address='+address;
    $.ajax({
        dataType: 'json',
        url: url,
        async: false,
        success: function(data) {
            if(data && data.status) {
                if(data.status == 'OK') {
                    retour = data.results[0];
                } else {
                    retour = false;

                }
            }
        }

    });
    return retour;
}

var map;
var contentString = '<p><strong>'+title+'</strong></p><p>'+description+'</p>';

var infowindow = new google.maps.InfoWindow({
    content: contentString
});

// on convertit l'adresse en coordonnées
var coord = geocodeService(address);
// Si l'adresse retourne des coordonnées correctes
if(coord != false) {
    var lat = coord.geometry.location.lat;
    var lon = coord.geometry.location.lng;
}

function initMap() {
    map = new google.maps.Map(document.getElementById('gmap-store-location'), {
        center: {lat: lat, lng: lon}, // center by bounds
        zoom: 10,
        scrollwheel: 0
    });
    setMarker(map);

};
function setMarker(map) {
    var marker = new google.maps.Marker({
        position: {lat: lat, lng: lon},
        map: map
    });

    infowindow.open(map, marker);
};

google.maps.event.addDomListener(window, 'load', initMap);