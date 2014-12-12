create table apps(
    id int not null auto_increment primary key,
    appkey varchar(32) not null,
    secret varchar(32) not null,
    user int not null,
    title varchar(255) not null,
    url varchar(255),
    description text
);