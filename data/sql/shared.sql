create table shared(
    id varchar(16) primary key,
    user int not null,
    goal int not null,
    data text
);

/* alter table shared add goal int not null after user; */
