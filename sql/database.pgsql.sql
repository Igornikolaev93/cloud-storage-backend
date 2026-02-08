--
-- SQL script for creating the PostgreSQL database structure for the Cloud Storage project.
--

-- Enable the extension for generating UUIDs if needed in the future.
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Drop tables with CASCADE to ensure a clean start, removing all dependent objects.
DROP TABLE IF EXISTS "file_shares" CASCADE;
DROP TABLE IF EXISTS "password_resets" CASCADE;
DROP TABLE IF EXISTS "files" CASCADE;
DROP TABLE IF EXISTS "directories" CASCADE;
DROP TABLE IF EXISTS "users" CASCADE;

--
-- Table: users
-- Stores user account information.
--
CREATE TABLE "users" (
    "id" SERIAL PRIMARY KEY,
    "username" VARCHAR(50) NOT NULL UNIQUE,
    "email" VARCHAR(100) NOT NULL UNIQUE,
    "password_hash" VARCHAR(255) NOT NULL,
    "role" VARCHAR(20) NOT NULL DEFAULT 'user', -- Added role column
    "created_at" TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE "users" IS 'Stores user accounts.';
COMMENT ON COLUMN "users"."password_hash" IS 'Password hash created with password_hash().';

--
-- Table: directories
-- Stores information about user-created directories.
--
CREATE TABLE "directories" (
    "id" SERIAL PRIMARY KEY,
    "user_id" INTEGER NOT NULL,
    "parent_id" INTEGER NULL, -- Null for root-level directories
    "name" VARCHAR(255) NOT NULL,
    "created_at" TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "fk_user"
        FOREIGN KEY("user_id") 
        REFERENCES "users"("id")
        ON DELETE CASCADE, -- Deleting a user deletes their directories
    CONSTRAINT "fk_parent"
        FOREIGN KEY("parent_id") 
        REFERENCES "directories"("id")
        ON DELETE CASCADE -- Deleting a parent directory deletes its children
);

CREATE INDEX "idx_directory_user_id" ON "directories"("user_id");
-- A user cannot have two directories with the same name under the same parent.
CREATE UNIQUE INDEX "idx_unique_directory_name" ON "directories"("user_id", "parent_id", "name");


--
-- Table: files
-- Stores metadata about uploaded files.
--
CREATE TABLE "files" (
    "id" SERIAL PRIMARY KEY,
    "user_id" INTEGER NOT NULL,
    "directory_id" INTEGER NULL, -- Null for files in the root directory
    "file_name" VARCHAR(255) NOT NULL,
    "file_path" VARCHAR(512) NOT NULL UNIQUE,
    "file_size" BIGINT NOT NULL,
    "mime_type" VARCHAR(100) NOT NULL,
    "upload_date" TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "access_token" VARCHAR(255) NULL UNIQUE,
    CONSTRAINT "fk_user"
        FOREIGN KEY("user_id") 
        REFERENCES "users"("id")
        ON DELETE CASCADE,
    CONSTRAINT "fk_directory"
        FOREIGN KEY("directory_id")
        REFERENCES "directories"("id")
        ON DELETE SET NULL -- If a directory is deleted, move files to the root
);

CREATE INDEX "idx_file_user_id" ON "files"("user_id");
CREATE INDEX "idx_file_directory_id" ON "files"("directory_id");

COMMENT ON TABLE "files" IS 'Stores metadata about uploaded files.';

--
-- Table: password_resets
-- Stores tokens for password resets.
--
CREATE TABLE "password_resets" (
    "id" SERIAL PRIMARY KEY,
    "user_id" INTEGER NOT NULL,
    "token" VARCHAR(255) NOT NULL UNIQUE,
    "expires_at" TIMESTAMPTZ NOT NULL,
    CONSTRAINT "fk_user"
        FOREIGN KEY("user_id") 
        REFERENCES "users"("id")
        ON DELETE CASCADE
);

CREATE INDEX "idx_password_token" ON "password_resets"("token");

-- Trigger function to automatically update the updated_at field
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
   NEW.updated_at = NOW();
   RETURN NEW;
END;
$$ language 'plpgsql';

-- Apply the trigger to the users table
CREATE TRIGGER "update_users_updated_at"
BEFORE UPDATE ON "users"
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();
