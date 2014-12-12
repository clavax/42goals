create table notifications (
    id int unsigned not null primary key auto_increment,
    user int unsigned not null,
    text varchar(255),
    url varchar(255),
    is_read enum('no', 'yes') default 'no',
    time datetime
) default charset = utf8;