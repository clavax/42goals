create table plan (
    id int unsigned auto_increment primary key,
    goal int unsigned not null,
    startdate date not null,
    enddate date not null,
    value text,
    text text
);