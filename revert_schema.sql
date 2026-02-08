-- Удалить и пересоздать таблицу files
DROP TABLE IF EXISTS files CASCADE;

CREATE TABLE "files" (
    "id" SERIAL PRIMARY KEY,
    "user_id" INTEGER NOT NULL,
    "directory_id" INTEGER NULL,
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
        ON DELETE SET NULL
);

CREATE INDEX "idx_file_user_id" ON "files"("user_id");
CREATE INDEX "idx_file_directory_id" ON "files"("directory_id");