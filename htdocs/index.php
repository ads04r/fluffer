<?php

$config = json_decode(file_get_contents("../cfg/webservice.json"), true);

$f3 = require($config['f3']); // Replace with the path of your F3 install
$f3->set('DEBUG', true);
$f3->set('page_template', "");

// Data functions

function getSpacesFromPoint($keyword, $lat, $lon, $dist)
{
	global $config;

	return array();
}

function getSpacesWithBoundingBox($keyword, $lat1, $lon1, $lat2, $lon2)
{
	global $config;

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

	$link = pg_connect("host=" . $config['database']['host'] . " dbname=" . $config['database']['database'] . " user=" . $config['database']['username'] . " password=" . $config['database']['password']);
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
	global $config;

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

function getKeywords()
{
	global $config;

	$query = "select distinct keyword from fluff_keywords;";
	$link = pg_connect("host=" . $config['database']['host'] . " dbname=" . $config['database']['database'] . " user=" . $config['database']['username'] . " password=" . $config['database']['password']);
	$result = pg_exec($link, $query);
	$numrows = pg_numrows($result);

	$keywords = array();

	for($ri = 0; $ri < $numrows; $ri++)
	{
		$row = pg_fetch_array($result, $ri);
		$keywords[] = $row['keyword'];
	}

	return($keywords);
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

	$command = $params['command'];
	$param = $params['param'];

	if(strcmp($command, "areas") == 0)
	{
		if(array_key_exists("bounding", $_REQUEST))
		{
			$bounding = $_REQUEST['bounding'];
			if(strlen($bounding) == 0)
			{
				$bb = array();
			} else {
				$bb = explode(",", $bounding);
			}
			if(count($bb) == 4)
			{
				$output = getSpacesWithBoundingBox($param, $bb[1], $bb[0], $bb[3], $bb[2]);
			}
		}

		elseif(($array_key_exists("distance"), $_REQUEST) & ($array_key_exists("centre"), $_REQUEST))
		{
			$sll = $_REQUEST['centre'];
			if(strlen($sll) == 0)
			{
				$ll = array();
			} else {
				$ll = explode(",", $sll);
			}
			if(count($ll) == 2)
			{
				$output = getSpacesFromPoint($param, $ll[1], $ll[0]);
			}
		}

	}

	if(strcmp($command, "keywords") == 0)
	{
		$output = getKeywords();
	}

	header("Content-type: application/json");
	print(json_encode($output));
}

// Routes

$f3->route("GET /", function($f3) { $f3->error(404); });
$f3->route("GET|POST /@command/@param.@format", 'render');
$f3->route("GET *", function($f3) { $f3->error(404); });

$f3->run();
