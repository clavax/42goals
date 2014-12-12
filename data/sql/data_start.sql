create table data_start (
    goal int not null,
    date date not null,
    start datetime,
    primary key(goal, date)
);
