-- $Id: install_proto.sql,v 1.3 2004/01/03 04:35:20 wendall911 Exp $

CREATE TABLE mod_phpwslistings_categories (
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
    use_mortcalc smallint NOT NULL DEFAULT 0,
    use_price   smallint NOT NULL DEFAULT 1,
    default_cat smallint NOT NULL DEFAULT 0,
    primary key (id)
);


CREATE TABLE mod_phpwslistings_items (
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
    cat_id      int NOT NULL,
    agent_id    int NOT NULL DEFAULT 0,
    notes       text,
    expiration  int NOT NULL default '0',
    hits        int NOT NULL DEFAULT 0,
    sold        smallint NOT NULL DEFAULT 0,
    solddate    int NULL default null,
    price       numeric(16,2),
    feature     smallint NOT NULL DEFAULT 1,
    primary key (id)
);
CREATE INDEX phpwslistings_items_cat_id ON mod_phpwslistings_items (cat_id);
CREATE INDEX phpwslistings_items_agent_id ON mod_phpwslistings_items (agent_id);


CREATE TABLE mod_phpwslistings_fields (
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
    cat_id      int NOT NULL,
    field_type  varchar(20) NOT NULL DEFAULT '',
    field_caption varchar(80) NOT NULL DEFAULT '',
    default_text text NOT NULL,
    rank        int NOT NULL DEFAULT 0,
    required    smallint NOT NULL DEFAULT 0,
    agentonly   smallint NOT NULL DEFAULT 0,
    location    varchar(15) NOT NULL DEFAULT '',
    display_on_browse smallint NOT NULL DEFAULT 0,
    primary key (id)
);
CREATE INDEX phpwslistings_fields_cat_id ON mod_phpwslistings_fields (cat_id);


CREATE TABLE mod_phpwslistings_selectitems (
    id     int NOT NULL,
    field_id    int NOT NULL,
    value       text,
    primary key (id)
);
CREATE INDEX phpwslistings_selectitems_field_id ON mod_phpwslistings_selectitems (field_id);


CREATE TABLE mod_phpwslistings_data (
    item_id     int NOT NULL,
    field_id    int NOT NULL,
    value       text,
    primary key (item_id,field_id)
);
CREATE INDEX phpwslistings_data_value ON mod_phpwslistings_data (value(16));


CREATE TABLE mod_phpwslistings_images (
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
    image       text NOT NULL,
    description text NOT NULL,
    item_id  int NOT NULL,
    agent_id    int NOT NULL DEFAULT 0,
    rank int    NOT NULL default 0,
    primary key (id)
);
CREATE INDEX phpwslistings_images_item_id ON mod_phpwslistings_images (item_id);
CREATE INDEX phpwslistings_images_agent_id ON mod_phpwslistings_images (agent_id);


CREATE TABLE mod_phpwslistings_agencies (
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
    image       text NULL,
    description text NOT NULL,
    primary key (id)
);

CREATE TABLE mod_phpwslistings_settings (
    show_feature    smallint NOT NULL DEFAULT 1,
    show_block      smallint NOT NULL DEFAULT 1,
    num_block       int NOT NULL DEFAULT 2,
    block_images_only smallint NOT NULL DEFAULT 0,
    listing_limit   int NOT NULL DEFAULT 0,
    imagesize_limit int NOT NULL,
    image_max_width int NOT NULL,
    image_max_height int NOT NULL,
    image_width_redim int NOT NULL,
    image_height_redim int NOT NULL,
    listing_image_limit int NOT NULL DEFAULT 3,
    feature_title   varchar(80) NOT NULL DEFAULT 'Featured Listings',
    block_title     varchar(80) NOT NULL DEFAULT 'Random Listings',
    show_menu       smallint NOT NULL DEFAULT 1,
    use_expiration  smallint NOT NULL DEFAULT 0,
    custom_layout   int NOT NULL DEFAULT 0
);

INSERT INTO mod_phpwslistings_settings VALUES (1,1,2,0,0,100,800,600,400,300,3,'Featured Listings','Random Listings',1,0,0);
