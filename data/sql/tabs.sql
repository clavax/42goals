create table tabs (
    id int unsigned primary key auto_increment,
    user int unsigned not null,
    title varchar(255) not null,
    position smallint unsigned not null default 0
) default charset = utf8;