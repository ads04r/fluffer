drop table fluff_keywords;

create table fluff_keywords as 
select osm_id, 'pub' as keyword from planet_osm_polygon where amenity in ('pub','bar')
union 
select osm_id, 'placeofworship' as keyword from planet_osm_polygon where amenity in ('place_of_worship')
union 
select osm_id, 'cemetary' as keyword from planet_osm_polygon where landuse in ('cemetery') or amenity in ('grave_yard')
union 
select osm_id, 'indoors' as keyword from planet_osm_polygon where building is not null 
union 
select osm_id, 'urban' as keyword from planet_osm_polygon where landuse in ('residential','retail') 
union 
select osm_id, 'water' as keyword from planet_osm_polygon where "natural" in ('water') or waterway in ('river')
union 
select osm_id, 'trees' as keyword from planet_osm_polygon where "natural" in ('wood') or landuse in ('forest','wood')
union 
select osm_id, 'green' as keyword from planet_osm_polygon where leisure in ('common','park','golf_course') or "natural" in ('wood') or landuse in ('grass','forest','wood') or landcover in ('grass')
;
