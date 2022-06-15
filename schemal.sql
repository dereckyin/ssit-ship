ALTER TABLE contact_us
ADD COLUMN `way` varchar(10) DEFAULT 'sea' AFTER source;

UPDATE contact_us set way = 'sea' where way = '';