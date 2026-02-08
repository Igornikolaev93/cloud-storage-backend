-- This script reverts the schema for the 'files' table back to using 'directory_id'.
-- Please execute it in your database management tool.

-- 1. Drop the 'parent_id' foreign key constraint if it exists.
ALTER TABLE files DROP CONSTRAINT IF EXISTS fk_parent_directory;

-- 2. Drop the 'parent_id' column if it exists.
ALTER TABLE files DROP COLUMN IF EXISTS parent_id;

-- 3. Add the 'directory_id' column back.
ALTER TABLE files ADD COLUMN directory_id INTEGER;

-- 4. Add a foreign key constraint to link 'files.directory_id' to 'directories.id'.
ALTER TABLE files ADD CONSTRAINT fk_directory
    FOREIGN KEY (directory_id)
    REFERENCES directories (id)
    ON DELETE SET NULL; -- This means if a directory is deleted, the file's directory_id will become NULL (it will be in the root).
