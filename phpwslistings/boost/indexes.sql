-- $Id: indexes.sql,v 1.1 2003/12/23 16:24:35 rizzo Exp $

-- LISTINGS
CREATE INDEX listings_classid_idx ON mod_listings (class_id);
CREATE INDEX listings_agentid_idx ON mod_listings (agent_id);

-- FORMELEMENTS
CREATE INDEX formelements_classid_idx ON mod_listings_formelements (class_id);

-- IMAGES
CREATE INDEX images_listingid_idx ON mod_listings_images (listing_id);
CREATE INDEX images_agentid_idx ON mod_listings_images (agent_id);
