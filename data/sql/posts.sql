create table posts (
    id int unsigned not null primary key auto_increment,
    user int unsigned not null,
    date datetime default NULL,
    title varchar(255) not null,
    url varchar(255),
    community int unsigned default null,
    text text,
    type enum('post', 'link') default 'post', 
    draft tinyint
) default charset = utf8;

-- alter table posts add url varchar(255);
-- alter table posts add type enum('post', 'link') default 'post';
