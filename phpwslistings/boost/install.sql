-- $Id: install.sql,v 1.20 2003/05/16 20:53:17 dtseiler Exp $

CREATE TABLE mod_listings (
    id int NOT NULL,
    class_id int NOT NULL,
    agent_id int NOT NULL DEFAULT 0,
    title varchar(80) NOT NULL DEFAULT '',
    notes text,
    creationdate date NOT NULL,
    lastmodified timestamp NOT NULL,
    expiration date,
    hits int NOT NULL DEFAULT 0,
    active smallint NOT NULL DEFAULT 1,
    sold smallint NOT NULL DEFAULT 0,
    solddate date null,
    price numeric(16,2),
    feature smallint NOT NULL DEFAULT 1,
    listelements text,
    primary key (id)
);

CREATE TABLE mod_listings_classes (
    id int NOT NULL,
    name varchar(50) NOT NULL,
    use_mortcalc smallint NOT NULL DEFAULT 0,
    use_price smallint NOT NULL DEFAULT 1,
    active smallint NOT NULL DEFAULT 1,
    default_class smallint NOT NULL DEFAULT 0,
    primary key (id)
);

CREATE TABLE mod_listings_formelements (
    id int NOT NULL,
    class_id int NOT NULL,
    field_type varchar(20) NOT NULL DEFAULT '',
    field_name varchar(20) NOT NULL DEFAULT '',
    field_caption varchar(80) NOT NULL DEFAULT '',
    default_text text NOT NULL,
    field_elements text NOT NULL,
    rank int NOT NULL DEFAULT 0,
    required smallint NOT NULL DEFAULT 0,
    agentonly smallint NOT NULL DEFAULT 0,
    location varchar(15) NOT NULL DEFAULT '',
    display_on_browse smallint NOT NULL DEFAULT 0,
    primary key (id)
);

CREATE TABLE mod_listings_images (
    id int NOT NULL,
    caption varchar(255) NOT NULL,
    image text NOT NULL,
    description text NOT NULL,
    listing_id int NOT NULL,
    agent_id int NOT NULL DEFAULT 0,
    rank int NOT NULL default 0,
    primary key (id)
);

CREATE TABLE mod_listings_agencies (
    id int NOT NULL,
    name varchar(255) NOT NULL,
    image text NULL,
    description text NOT NULL,
    active smallint NOT NULL DEFAULT 1,
    primary key (id)
);

CREATE TABLE mod_listings_settings (
    paginate_limit int NOT NULL DEFAULT 10,
    show_feature smallint NOT NULL DEFAULT 1,
    show_block smallint NOT NULL DEFAULT 1,
    num_block int NOT NULL DEFAULT 2,
    block_images_only smallint NOT NULL DEFAULT 0,
    listing_limit int NOT NULL DEFAULT 0,
    imagesize_limit int NOT NULL,
    image_max_width int NOT NULL,
    image_max_height int NOT NULL,
    image_width_redim int NOT NULL,
    image_height_redim int NOT NULL,
    listing_image_limit int NOT NULL DEFAULT 3,
    feature_title varchar(80) NOT NULL DEFAULT 'Featured Listings',
    block_title varchar(80) NOT NULL DEFAULT 'Random Listings',
    show_menu smallint NOT NULL DEFAULT 1,
    use_expiration smallint NOT NULL DEFAULT 0,
    custom_layout int NOT NULL DEFAULT 0
);

INSERT INTO mod_listings_settings VALUES (10,1,1,2,0,0,100,800,600,400,300,3,'Featured Listings','Random Listings',1,0,0);
