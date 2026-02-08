-- This script corrects the schema for the 'files' table.
-- Please execute it in your database management tool.

-- 1. Drop the incorrect 'directory_id' column if it exists.
ALTER TABLE files DROP COLUMN IF EXISTS directory_id;

-- 2. Add the correct 'parent_id' column to link to the 'directories' table.
ALTER TABLE files ADD COLUMN parent_id INTEGER;

-- 3. Add a foreign key constraint to ensure data integrity.
-- This links 'files.parent_id' to 'directories.id'.
ALTER TABLE files ADD CONSTRAINT fk_parent_directory
    FOREIGN KEY (parent_id)
    REFERENCES directories (id)
    ON DELETE SET NULL; -- This means if a directory is deleted, the file's parent_id will become NULL (it will be in the root).
