function initMap(self) {

    var mapnikTileUrl = 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';

    self.osmTileLayer = L.tileLayer(mapnikTileUrl, {
        maxZoom: 18,
        attribution: ""
    });

    var baseMaps = {
        "Standard": self.osmTileLayer
    };

    // Create the map
    var map = L.map( 'map', {
        zoom: 12,
        layers: [self.osmTileLayer]
    }).setView([50.9354, -1.3964], 17);

    L.control.scale().addTo(map);

    var emptyFeatureCollection = { type: "FeatureCollection", features: [] };

    self.layers = {}

    $.each(self.keywords, function(index, keyword) {
        self.layers[keyword] = L.geoJson(emptyFeatureCollection, {
            style: {color: "#0000ff"},
            onEachFeature: function(feature, layer) {
            }
        });
    });

    L.control.layers(baseMaps, self.layers).addTo(map);

    return map;
}

function updateMap(self) {

    var bb = self.map.getBounds().toBBoxString();

    $.each(self.keywords, function(index, keyword) {
        $.post('/places/areas/' + keyword + '.json', bb, function(data) {
                self.layers[keyword].clearLayers();
                self.layers[keyword].addData(data);
        }, "json");
    });


}

$(document).ready(function() {

	var self = this;  // the HTMLDocument

    $.get('/places/keywords/keywords.json', function(data) {
        self.keywords = data;

        self.map = initMap(self);

        updateMap(self);

        self.map.on('moveend', function(e) {
            updateMap(self);
        });
    }, "json");


});

