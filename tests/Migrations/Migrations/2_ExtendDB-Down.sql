-- The migration script for downgrading the database schema from version 2 to 1
-- ----------------------------------------------------------------------------

-- Remove the 'Properties' table
DROP TABLE IF EXISTS `Properties`;