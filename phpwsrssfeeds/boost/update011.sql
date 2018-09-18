$Id: update011.sql,v 1.1 2003/12/02 19:00:53 wendall911 Exp $

CREATE TABLE mod_phpwsrssfeeds_backend (
    id                      int NOT NULL default '0',
    owner               varchar(20) default '',
    editor               varchar(20) default '',
    ip                      text,
    label                  text NOT NULL,
    groups              text,
    created             int NOT NULL default '0',
    updated            int NOT NULL default '0',
    hidden              smallint NOT NULL default '0',
    approved          smallint NOT NULL default '1',
    numitems         smallint NULL,
    type                  text,
    description       text,
    image               text,
    PRIMARY KEY (id)
);