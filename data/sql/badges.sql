create table badges(
    user int unsigned not null,
    type varchar(64) not null,
    date date,
    key(user)
);