create table connections(
    user_from int unsigned not null,
    user_to int unsigned not null,
    status enum('requested', 'accepted', 'rejected')
) default charset utf8;