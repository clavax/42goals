create table data(
    user int unsigned not null,
    goal int unsigned not null,
    date date not null,
    value text,
    text text,
    created date,
    modified datetime,
    primary key(goal, date)
);

/* alter table data add text text; */
-- update data set value = 'yes' where value = 'true';
-- update data set value = 'no' where value = 'false';

-- alter table data add user int unsigned not null;
-- alter table data add created date;
-- alter table data modify created date;
-- alter table data add modified datetime;
-- update data set created = date, modified = date where date < now();
-- update data set created = now(), modified = now() where date > now();