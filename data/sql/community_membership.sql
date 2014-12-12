create table community_membership (
    user int unsigned not null,
    community int unsigned not null,
    role enum('member', 'admin') default member,
    time datetime
);

-- alter table community_membership add role enum('member', 'admin') default 'member';
-- alter table community_membership add time datetime;