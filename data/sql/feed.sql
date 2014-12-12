create table feed (
    id int unsigned not null primary key auto_increment,
    resource varchar(32) not null unique,
    user int unsigned,
    community int unsigned,
    type varchar(32) not null,
    data text,
    time datetime
) default charset = utf8;

-- alter table feed add resource varchar(32) not null unique;