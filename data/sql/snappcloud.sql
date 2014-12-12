create table snappcloud(
    id int unsigned auto_increment primary key,
    user int unsigned not null,
    order_id int unsigned not null,
    date datetime,
    data text,
    hash varchar(32) not null
);

-- alter table snappcloud add order_id int unsigned not null; 