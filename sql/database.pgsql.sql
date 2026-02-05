--
-- SQL-скрипт для создания структуры базы данных PostgreSQL для проекта Cloud Storage.
--

-- Включаем расширение для генерации UUID, если оно понадобится в будущем.
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Удаляем таблицы и все зависимые объекты (CASCADE) для чистого старта.
-- Это необходимо, потому что между таблицами есть связи (foreign keys).
DROP TABLE IF EXISTS "file_shares" CASCADE;
DROP TABLE IF EXISTS "password_resets" CASCADE;
DROP TABLE IF EXISTS "files" CASCADE;
DROP TABLE IF EXISTS "users" CASCADE;

--
-- Таблица: users
-- Хранит информацию о пользователях.
--
CREATE TABLE "users" (
    "id" SERIAL PRIMARY KEY,
    "username" VARCHAR(50) NOT NULL UNIQUE,
    "email" VARCHAR(100) NOT NULL UNIQUE,
    "password_hash" VARCHAR(255) NOT NULL,
    "created_at" TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

COMMENT ON TABLE "users" IS 'Хранит учетные записи пользователей.';
COMMENT ON COLUMN "users"."password_hash" IS 'Хэш пароля, созданный с помощью password_hash().';

--
-- Таблица: files
-- Хранит информацию о загруженных файлах.
--
CREATE TABLE "files" (
    "id" SERIAL PRIMARY KEY,
    "user_id" INTEGER NOT NULL,
    "file_name" VARCHAR(255) NOT NULL,
    "file_path" VARCHAR(512) NOT NULL UNIQUE, -- Уникальный путь в файловой системе
    "file_size" BIGINT NOT NULL, -- Размер в байтах
    "mime_type" VARCHAR(100) NOT NULL,
    "upload_date" TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "access_token" VARCHAR(255) NULL UNIQUE, -- Токен для публичного доступа
    CONSTRAINT "fk_user"
        FOREIGN KEY("user_id") 
        REFERENCES "users"("id")
        ON DELETE CASCADE -- Удалять файлы пользователя, если удаляется сам пользователь.
);

CREATE INDEX "idx_user_id" ON "files"("user_id");

COMMENT ON TABLE "files" IS 'Хранит метаданные о загруженных файлах.';
COMMENT ON COLUMN "files"."access_token" IS 'Токен для генерации публичных ссылок на файл.';


--
-- Таблица: password_resets
-- Хранит токены для сброса пароля.
--
CREATE TABLE "password_resets" (
    "id" SERIAL PRIMARY KEY,
    "email" VARCHAR(100) NOT NULL,
    "token" VARCHAR(255) NOT NULL UNIQUE,
    "expires_at" TIMESTAMPTZ NOT NULL,
    "created_at" TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX "idx_token" ON "password_resets"("token");

COMMENT ON TABLE "password_resets" IS 'Хранит токены для сброса паролей пользователей.';

-- Обновление функции для автоматического обновления поля updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
   NEW.updated_at = NOW();
   RETURN NEW;
END;
$$ language 'plpgsql';

-- Применение триггера к таблице users
CREATE TRIGGER "update_users_updated_at"
BEFORE UPDATE ON "users"
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

