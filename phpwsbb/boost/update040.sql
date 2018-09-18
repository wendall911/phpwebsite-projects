-- $Id: update040.sql,v 1.2 2003/08/29 14:47:07 dtseiler Exp $
CREATE TABLE mod_phpwsbb_monitors (
    thread_id   int NOT NULL,
    user_id     int NOT NULL
);

CREATE TABLE mod_phpwsbb_settings (
    allow_anon_view     smallint    NOT NULL default '1',
    allow_anon_posts    smallint    NOT NULL default '1'
);
INSERT INTO mod_phpwsbb_settings VALUES (1, 1);
