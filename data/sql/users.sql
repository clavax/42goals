create table users(
    id int unsigned primary key auto_increment,
    login varchar(255) not null unique,
    password varchar(32) not null,
    email varchar(255) not null,
    name varchar(255) not null,
    email_confirmation varchar(32),
    password_recovery varchar(32),
    status enum('pending', 'active', 'twitter', 'facebook') not null default 'pending',
    new_email varchar(255),
    paid_till date default NULL,
    picture varchar(255) not null,
    thumbnail varchar(255) not null,
    location varchar(255) not null,
    url varchar(255) not null,
    bio varchar(255) not null,
    public tinyint not null default 0,
    geo varchar(33) not null,
    language char(2) not null,
    tw_id varchar(16),
    tw_token varchar(255),
    tw_secret varchar(255)
    fb_id varchar(16),
    fb_token varchar(255),
    registered date,
    receive_emails tinyint
) default charset utf8;

-- alter table users add email_confirmation varchar(32);
-- alter table users add password_recovery varchar(32);
-- alter table users add new_email varchar(255);
-- alter table users add paid_till date default NULL;
-- alter table users add picture varchar(255) not null;
-- alter table users add location varchar(255) not null;
-- alter table users add url varchar(255) not null;
-- alter table users add bio varchar(255) not null;
-- alter table users add thumbnail varchar(255) not null;
-- alter table users add public tinyint not null default 0;
-- alter table users add geo varchar(33) not null;
-- alter table users add language char(2) not null;

-- alter table users add tw_id varchar(16);
-- alter table users add tw_token varchar(255);
-- alter table users add tw_secret varchar(255);
-- alter table users add fb_id varchar(16);
-- alter table users add fb_token varchar(255);
-- alter table users modify status enum('pending', 'active', 'twitter', 'facebook') not null default 'pending';

-- alter table users add registered datetime;
-- alter table users modify registered date;

-- alter table users add receive_emails tinyint default 1;
