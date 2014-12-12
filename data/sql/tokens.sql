create table access_tokens(
    id varchar(32) not null unique primary key,
    secret varchar(32) not null,
    app int not null,
    user int,
    status varchar(16),
    created timestamp not null default current_timestamp
);

create table request_tokens(
    id varchar(32) not null unique primary key,
    secret varchar(32) not null,
    app int not null,
    user int,
    status varchar(16),
    created timestamp not null default current_timestamp
);