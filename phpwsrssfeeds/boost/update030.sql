CREATE TABLE mod_phpwsrssfeeds_multi (
    id                  INT NOT NULL DEFAULT '0',
    owner               VARCHAR(20) DEFAULT '',
    editor              VARCHAR(20) DEFAULT '',
    ip                  TEXT,
    label               TEXT NOT NULL,
    groups              TEXT,
    created             INT NOT NULL DEFAULT '0',
    updated             INT NOT NULL DEFAULT '0',
    hidden              SMALLINT NOT NULL DEFAULT '0',
    approved            SMALLINT NOT NULL DEFAULT '1',
    show_in_multiview   TEXT,
    max_multi_items     INT NOT NULL DEFAULT '20',
    home                SMALLINT NOT NULL DEFAULT '0',
    block               SMALLINT NOT NULL DEFAULT '0',
    allow_view          TEXT,
    pm_allow            TEXT,
    PRIMARY KEY (id)
);