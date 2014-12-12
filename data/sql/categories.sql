create table categories (
    id int unsigned primary key auto_increment,
    title_en varchar(255) not null,
    title_ru varchar(255) not null,
    title_fr varchar(255) not null,
    name varchar(255) not null,
    position smallint unsigned not null default 0
) default charset = utf8;

-- alter table categories add title_fr varchar(255) not null;