delimiter $$

drop function if exists `user_distance`$$

create function `user_distance`(coord1 varchar(120), coord2 varchar(120))
    returns decimal(12, 8)
begin
    declare lon1, lon2, lat1, lat2, distance decimal(12, 8);

    set lon1 = cast(substring_index(coord1, ':', +1) as decimal(12, 8));
    set lat1 = cast(substring_index(coord1, ':', -1) as decimal(12, 8));
    set lon2 = cast(substring_index(coord2, ':', +1) as decimal(12, 8));
    set lat2 = cast(substring_index(coord2, ':', -1) as decimal(12, 8));
    
    set distance = ((acos(sin(lat1 * pi() / 180) * sin(lat2 * pi() / 180) + cos(lat1 * pi() / 180) * cos(lat2 * pi() / 180) * cos((lon1 - lon2) * pi() / 180)) * 180 / pi()) * 60 * 1.1515);
    return distance;

end$$

delimiter ;