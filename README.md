Fluffer
=======

Web service for generating geoJSON from 'fluffy' descriptions, such as 'green', 'urban', 'indoors', etc.
Created as part of WAISfest 2013.

Documentation follows.

Install OpenStreetMap server
----------------------------

Install OSM2PGSQL
Follow instructions for setting up a tile server:
http://switch2osm.org/serving-tiles/manually-building-a-tile-server-12-04/
You need to install PostGreSQL and PostGIS first, Mapnik isn't necessary.

Download Hampshire map from 
http://download.geofabrik.de/europe/great-britain/england/hampshire.html

Import file downloaded from GeoFabrik into the PostGreSQL database using OSM2PGSQL.

Create Keywords for Polygons
----------------------------

Import the files in the './sql' directory into the PostGreSQL database.
psql -d [database] -f [filename]
Do polygons first, then keywords.

Install web service
-------------------

Install fatfree framework
Copy index.php to htdocs
Modify cfg/webservice.json to reflect database settings.

