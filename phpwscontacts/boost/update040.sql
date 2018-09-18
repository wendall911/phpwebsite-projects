-- $Id: update040.sql,v 1.2 2003/08/27 02:54:51 dtseiler Exp $
CREATE TABLE mod_phpwscontacts_settings (
    allow_anon_view     smallint    NOT NULL default '1',
    sortbyfirstname     smallint NOT NULL default '0'
);
INSERT INTO mod_phpwscontacts_settings VALUES (1,0);
