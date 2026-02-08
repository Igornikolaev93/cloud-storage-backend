ALTER TABLE files DROP COLUMN IF EXISTS directory_id;
ALTER TABLE files ADD COLUMN parent_id INTEGER;
ALTER TABLE files ADD CONSTRAINT fk_parent_directory
    FOREIGN KEY (parent_id)
    REFERENCES directories (id)
    ON DELETE SET NULL;