create table icons(
    id int unsigned auto_increment primary key,
    src varchar(255) not null,
    user int unsigned not null,
    position smallint unsigned not null default 0
);