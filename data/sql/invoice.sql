create table invoices (
    id int unsigned not null auto_increment primary key,
    user int not null,
    date date not null,
    number bigint unsigned not null,
    type varchar(16) not null,
    quantity smallint unsigned not null,
    data text
);