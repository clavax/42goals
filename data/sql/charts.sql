create table charts(
    id int unsigned not null auto_increment primary key,
    user int unsigned not null,
    goal int unsigned not null,
    position tinyint not null,
    title varchar(255) not null,
    type enum('column', 'bar', 'line', 'area', 'pie') not null,
    period enum('week', 'month', 'quarter', 'year') not null,
    groupby enum('day', 'week', 'month', 'weekday') not null,
    accumulate tinyint not null,
    interpolate tinyint not null,
    fill_empty tinyint not null
) default charset = utf8;