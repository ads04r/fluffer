<?php

$f3 = require("fatfree/lib/base.php"); // Replace with the path of your F3 install
$f3->set('DEBUG', true);
$f3->set('page_template', "");

// Data functions

function getSpacesWithBoundingBox($keyword, $lat1, $lon1, $lat2, $lon2)
{
	if($lat1 > $lat2)
	{
		$maxlat = $lat1;
		$minlat = $lat2;
	} else {
		$maxlat = $lat2;
		$minlat = $lat1;
	}
	if($lon1 > $lon2)
	{
		$maxlon = $lon1;
		$minlon = $lon2;
	} else {
		$maxlon = $lon2;
		$minlon = $lon1;
	}

	$query = "select name, ST_AsGeoJSON(way) as json from (select fluff_polygons.name, way from fluff_keywords, fluff_polygons where fluff_keywords.osm_id=fluff_polygons.osm_id and keyword='" . $keyword . "') as sq where way && ST_MakeEnvelope(" . $minlon . ", " . $minlat . ", " . $maxlon . ", " . $maxlat . ", 4326);";

	print($query . "\n\n");

	$link = pg_connect("host=marbles.ecs.soton.ac.uk dbname=hampshire user=ash password=password");
	$result = pg_exec($link, $query);
	$numrows = pg_numrows($result);

	$features = array();

	for($ri = 0; $ri < $numrows; $ri++)
	{
		$feature = array();
		$properties = array();

		$row = pg_fetch_array($result, $ri);
		$name = "";
		$name = @$row['name'];
		if(strlen($name) > 0)
		{
			$properties['name'] = $name;
		}
		$properties['keyword'] = $keyword;
		$json = json_decode($row['json']);
		$feature['type'] = "Feature";
		$feature['geometry'] = $json;
		$feature['properties'] = $properties;

		$features[] = $feature;
	}

	$list = array();
	$list['type'] = "FeatureCollection";
	$list['features'] = $features;

	return($list);
}

function getSpaces($keyword)
{
	$link = pg_connect("host=marbles.ecs.soton.ac.uk dbname=hampshire user=ash password=password");
	$result = pg_exec($link, "select planet_osm_polygon.name, ST_AsGeoJSON(ST_Transform(way, 4326)) as json from fluff_keywords, planet_osm_polygon where fluff_keywords.osm_id=planet_osm_polygon.osm_id and keyword='" . $keyword . "';");
	$numrows = pg_numrows($result);

	$features = array();

	for($ri = 0; $ri < $numrows; $ri++)
	{
		$feature = array();
		$properties = array();

		$row = pg_fetch_array($result, $ri);
		$name = "";
		$name = @$row['name'];
		if(strlen($name) > 0)
		{
			$properties['name'] = $name;
		}
		$properties['keyword'] = $keyword;
		$json = json_decode($row['json']);
		$feature['type'] = "Feature";
		$feature['geometry'] = $json;
		$feature['properties'] = $properties;

		$features[] = $feature;
	}

	$list = array();
	$list['type'] = "FeatureCollection";
	$list['features'] = $features;

	return($list);
}

// Render Function

function render($f3, $params)
{
	$format = $params['format'];
	if(strcmp($format, "json") != 0)
	{
		$f3->error(404);
		exit();
	}

	$post = file_get_contents("php://input");
	if(strlen($post) == 0)
	{
		$bb = array();
	} else {
		$bb = explode(",", $post);
	}
	if(count($bb) != 4)
	{
		$bb = array();
	}

	// sw_lon,sw_lat,ne_lon,ne_lat

	$command = $params['command'];
	$param = $params['param'];
	$output = array();
	if(strcmp($command, "areas") == 0)
	{
		if(count($bb) == 4)
		{
			$output = getSpacesWithBoundingBox($param, $bb[1], $bb[0], $bb[3], $bb[2]);
		} else {
			$output = getSpaces($param);
		}
	}

	header("Content-type: application/json");
	print(json_encode($output));
}

// Routes

$f3->route("GET /", function($f3) { $f3->error(404); });
$f3->route("GET /@command/@param.@format", 'render');
$f3->route("POST /@command/@param.@format", 'render');
$f3->route("GET *", function($f3) { $f3->error(404); });

$f3->run();
