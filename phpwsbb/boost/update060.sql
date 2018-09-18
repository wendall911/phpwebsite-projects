-- $Id: update060.sql,v 1.2 2003/11/02 04:54:53 rizzo Exp $
CREATE TABLE mod_phpwsbb_forums (
    id          int NOT NULL default '0',
    owner       varchar(20) default '',
    editor      varchar(20) default '',
    ip          text,
    label       text NOT NULL,
    groups      text,
    created     int NOT NULL default '0',
    updated     int NOT NULL default '0',
    hidden      smallint NOT NULL default '1',
    approved    smallint NOT NULL default '0',
    description text,
    threads     int NOT NULL default '0',
    sortorder   int NOT NULL default '0',
    PRIMARY KEY (id)
);

CREATE TABLE mod_phpwsbb_banned (
    id          int NOT NULL default '0',
    username    text default '',
    ip          text default '',
    PRIMARY KEY (id)
);

