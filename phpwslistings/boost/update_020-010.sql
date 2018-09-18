-- $Id: update_020-010.sql,v 1.8 2003/05/16 20:53:17 dtseiler Exp $

-- Add listing class table
CREATE TABLE mod_listings_classes (
    id int NOT NULL,
    name varchar(50) NOT NULL,
    use_mortcalc smallint NOT NULL DEFAULT 0,
    use_price smallint NOT NULL DEFAULT 1,
    active smallint NOT NULL DEFAULT 1,
    primary key (id)
);

-- Add class_id field to listings and formelements tables.
ALTER TABLE mod_listings ADD class_id int NOT NULL DEFAULT 1;
ALTER TABLE mod_listings_formelements ADD class_id int NOT NULL DEFAULT 1;

-- Add new settings
ALTER TABLE mod_listings_settings ADD use_price smallint NOT NULL DEFAULT 1;
ALTER TABLE mod_listings_settings ADD feature_title varchar(80) NOT NULL DEFAULT 'Featured Listings';
ALTER TABLE mod_listings_settings ADD block_title varchar(80) NOT NULL DEFAULT 'Random Listings';
ALTER TABLE mod_listings_settings ADD show_menu smallint NOT NULL DEFAULT 1;
