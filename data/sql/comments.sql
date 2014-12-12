create table comments (
    id int unsigned not null primary key auto_increment,
    user int unsigned not null,
    thread_type varchar(16) not null,
    thread_id varchar(16) not null,
    reply_to int unsigned not null,
    date datetime default null,
    text text,
    index(thread_type, thread_id)
) default charset utf8;