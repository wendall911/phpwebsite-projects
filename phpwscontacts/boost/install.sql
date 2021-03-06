-- $Id: install.sql,v 1.14 2004/08/09 03:35:56 rizzo Exp $

CREATE TABLE mod_phpwscontacts_contacts (
    id                  int NOT NULL default '0',
    owner               varchar(20) default '',
    editor              varchar(20) default '',
    ip                  text,
    label               text NOT NULL,
    groups              text,
    created             int NOT NULL default '0',
    updated             int NOT NULL default '0',
    hidden              smallint NOT NULL default '0',
    approved            smallint NOT NULL default '1',
    firstname           varchar(255) NOT NULL,
    middlename          varchar(255) NOT NULL,
    lastname            varchar(255) NOT NULL,
    maidenname          varchar(255) NOT NULL,
    prefix              varchar(255),
    suffix              varchar(255),
    gender              char(1),
    email               varchar(255),
    phone_home          varchar(255),
    phone_pager         varchar(255),
    phone_mobile        varchar(255),
    phone_work          varchar(255),
    phone_fax           varchar(255),
    phone_other         varchar(255),
    company_name        varchar(255),
    company_title       varchar(255),
    company_street      varchar(255),
    company_city        varchar(255),
    company_state       varchar(255),
    company_zip         varchar(255),
    company_country     varchar(255),
    company_website     varchar(255),
    personal_street     varchar(255),
    personal_city       varchar(255),
    personal_state      varchar(255),
    personal_zip        varchar(255),
    personal_country    varchar(255),
    personal_website    varchar(255),
    str_birthday        varchar(32),
    str_deathday        varchar(32),
    str_anniversary     varchar(32),
    altemail1           varchar(255),
    altemail2           varchar(255),
    comments            text,
    image               text,
    visibility          int NOT NULL default '0',
    mine                smallint NOT NULL default '0',
    custom1             varchar(255),
    custom2             varchar(255),
    custom3             varchar(255),
    custom4             varchar(255),
    PRIMARY KEY (id)
);

CREATE TABLE mod_phpwscontacts_settings (
    allow_anon_view     smallint NOT NULL default '1',
    sortbyfirstname     smallint NOT NULL default '0',
    custom1label        varchar(255),
    custom2label        varchar(255),
    custom3label        varchar(255),
    custom4label        varchar(255)
);
INSERT INTO mod_phpwscontacts_settings VALUES(1,0,'','','','');
