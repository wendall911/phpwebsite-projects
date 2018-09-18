-- $Id: install.sql,v 1.25 2006/03/01 14:43:16 singletrack Exp $

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
    lastpost    int NOT NULL default '0',
    posts       int NOT NULL default '0',
    lastpost_topic_label  text NOT NULL,
    lastpost_topic_id     int NOT NULL default '0',
    lastpost_post_id      int NOT NULL default '0',
    moderators  varchar(40) NOT NULL default '',
    PRIMARY KEY (id)
);

CREATE TABLE mod_phpwsbb_threads (
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
    fid         int NOT NULL default '0',
    sticky      smallint,
    locked      smallint,
    replies     int NOT NULL default '0',
    views       int NOT NULL default '0',
    lastpost    int NOT NULL default '0',
    lastpost_post_id      int NOT NULL default '0',
    PRIMARY KEY (id)
);
CREATE INDEX phpwsbb_threads_fid_idx ON mod_phpwsbb_threads (fid);

CREATE TABLE mod_phpwsbb_messages (
    id          int NOT NULL default '0',
    owner       varchar(20) default '',
    owner_id    int NOT NULL default '0',
    editor      varchar(20) default '',
    ip          text,
    label       text NOT NULL,
    groups      text,
    created     int NOT NULL default '0',
    updated     int NOT NULL default '0',
    hidden      smallint NOT NULL default '1',
    approved    smallint NOT NULL default '0',
    tid         int NOT NULL default '0',
    guestname   varchar(50),
    guestemail  varchar(80),
    body        text,
    PRIMARY KEY (id)
);
CREATE INDEX phpwsbb_messages_tid_idx ON mod_phpwsbb_messages (tid);
CREATE INDEX phpwsbb_messages_owner_id_idx ON mod_phpwsbb_messages (owner_id);

CREATE TABLE mod_phpwsbb_monitors (
    thread_id   int NOT NULL,
    user_id     int NOT NULL
);
CREATE INDEX phpwsbb_monitors_thread_id_idx ON mod_phpwsbb_monitors (thread_id);

CREATE TABLE mod_phpwsbb_banned (
    id          int NOT NULL default '0',
    username    text default '',
    ip          text default '',
    PRIMARY KEY (id)
);

CREATE TABLE mod_phpwsbb_settings (
    allow_anon_view         smallint    NOT NULL default '1',
    allow_anon_posts        smallint    NOT NULL default '1',
    admin_email             varchar(80) NULL,
    email_text              text,
    monitor_posts           smallint    NOT NULL default '0',
    allow_user_monitors     smallint    NOT NULL default '1',
    showforumsblock         smallint    NOT NULL default '0',
    forumsblocktitle        varchar(80) NULL,
    showlatestthreadsblock  smallint    NOT NULL default '0',
    latestthreadsblocktitle varchar(80) NULL,
    maxlatestthreads        int NOT NULL default '0',
    bboffline               smallint    NOT NULL default '0',
    use_avatars             smallint    NOT NULL default '1',
    use_offsite_avatars     smallint    NOT NULL default '0',
    max_avatar_height       int         NOT NULL default '90',
    max_avatar_width        int         NOT NULL default '90',
    max_avatar_size         int         NOT NULL default '6',
    use_signatures          smallint    NOT NULL default '1',
    use_views               smallint    NOT NULL default '0',
    use_low_priority        smallint    NOT NULL default '0',
    show_categories         smallint    NOT NULL default '1'
);
INSERT INTO mod_phpwsbb_settings VALUES (1, 1, NULL, 'The thread [name] has been updated.  Go to [url] to view it.', 0, 1, 1, 'Forums', 1, 'Latest Forum Posts', 5, 0, 1, 0, 90, 90, 6, 1, 0, 0, 1);

CREATE TABLE mod_phpwsbb_user_ranks (
    rank_id                 int         DEFAULT '0' NOT NULL, 
    rank_title              varchar(50) NOT NULL, 
    rank_min                smallint    DEFAULT '0' NOT NULL, 
    rank_special            smallint    DEFAULT '0', 
    rank_image              varchar(255), 
    rank_image_caption      varchar(255),
    PRIMARY KEY (rank_id)
);
INSERT INTO mod_phpwsbb_user_ranks VALUES ('Site Admin', 0, 1, NULL, NULL);
INSERT INTO mod_phpwsbb_user_ranks VALUES ('Member', 0, 0, NULL, NULL);

CREATE TABLE mod_phpwsbb_user_info (
    user_id           int NOT NULL,
    posts             int NOT NULL default '0',
    location          varchar(50) NOT NULL,
    avatar_dir        varchar(50) NOT NULL,
    avatar_file       varchar(100) NOT NULL,
    signature         varchar(255) NOT NULL,
    suspendmonitors   smallint NOT NULL default '0',
    monitordefault    smallint NOT NULL default '0',
    session_start     int NOT NULL default '0',
    last_on           int NOT NULL default '0',
    PRIMARY KEY (user_id)
);
