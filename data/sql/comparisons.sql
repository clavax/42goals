create table comparisons(
    id int unsigned not null primary key auto_increment,
    user int unsigned not null,
    comment varchar(255)
) default charset=utf8;

-- alter table comparisons add comment varchar(255);

create table comparisons_item(
    id int unsigned not null primary key auto_increment,
    comparison int unsigned not null,
    user int unsigned not null,
    goal int unsigned not null,
    status enum('requested', 'accepted', 'rejected')
);