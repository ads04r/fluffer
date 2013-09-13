# Create tables based on existing OSM data


# Create keywords table
create table fluff_keywords as select osm_id, 'indoors' as keyword from planet_osm_polygon where building is not null union select osm_id, 'urban' as keyword from planet_osm_polygon where landuse in ('residential','retail') union select osm_id, 'green' as keyword from planet_osm_polygon where leisure in ('common','park') or landuse in ('grass','forest','wood') or landcover in ('grass');

# Create polygons table
create table fluff_polygons as select osm_id, access, "addr:housename", "addr:housenumber", "addr:interpolation", admin_level, aerialway, aeroway, amenity, area, barrier, bicycle, brand, bridge, boundary, building, construction, covered, culvert, cutting, denomination, disused, embankment, foot, "generator:source", harbour, highway, historic, horse, intermittent, junction, landuse, layer, leisure, lock, man_made, military, motorcar, name, "natural", office, oneway, operator, place, population, power, power_source, public_transport, railway, ref, religion, route, service, shop, sport, surface, toll, tourism, "tower:type", tracktype, tunnel, water, waterway, wetland, width, wood, z_order, way_area, uri, landcover, ST_Transform(way, 4326) as way from planet_osm_polygon;
