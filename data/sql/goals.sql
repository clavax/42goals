create table goals(
    id int unsigned not null primary key auto_increment,
    user int unsigned not null,
    title varchar(255) not null,
    text text,
    type varchar(16) not null,
    icon_item int unsigned,
    icon_zero int unsigned,
    icon_true int unsigned,
    icon_false int unsigned,
    position smallint unsigned not null default 0,
    unit varchar(32),
    prepend enum('no', 'yes') not null default 'no',
    aggregate varchar(32) default 'sum',
    template int unsigned not null default 0,
    approved enum('no', 'yes') not null default 'no',
    tab int unsigned not null default 0,
    archived date default NULL,
    privacy enum('private', 'public') not null default 'private'
) default charset = utf8;

-- alter table goals add unit varchar(32);
-- alter table goals add prepend enum('no', 'yes') not null default 'no';
-- alter table goals add aggregate varchar(32) default 'sum';
-- alter table goals add template enum('no', 'yes') not null default 'no';
-- alter table goals add approved enum('no', 'yes') not null default 'no';
-- alter table goals add icon_zero int unsigned after icon_item;
-- alter table goals add tab int unsigned not null default 0;
-- alter table goals add archived date default NULL;
-- alter table goals modify template int unsigned not null default 0;
-- alter table goals add privacy enum('private', 'public') not null default 'private';