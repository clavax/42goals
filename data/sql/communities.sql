create table communities (
    id int unsigned not null auto_increment primary key,
    user int unsigned not null,
    title varchar(255) not null,
    name varchar(255) not null unique,
    picture varchar(255) not null,
    thumbnail varchar(255) not null,
    overview varchar(255) not null,
    description text,
    post_permission enum('all', 'admins') default 'all',
    language char(2) not null
) default charset = utf8;

-- alter table communities add picture varchar(255);
-- alter table communities add overview varchar(255);
-- alter table communities modify id int unsigned not null auto_increment;
-- alter table communities add thumbnail varchar(255);
-- alter table communities add unique key(name);
-- alter table communities add post_permission enum('all', 'admins') default 'all';
-- alter table communities add language char(2) not null;