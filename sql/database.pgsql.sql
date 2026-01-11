-- Удаляем таблицы в обратном порядке, чтобы избежать ошибок внешних ключей
DROP TABLE IF EXISTS file_shares;
DROP TABLE IF EXISTS files;
DROP TABLE IF EXISTS directories;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS users;
DROP TYPE IF EXISTS user_role;

-- Создаем кастомный тип для ролей пользователей
CREATE TYPE user_role AS ENUM ('user', 'admin');

-- Таблица пользователей
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    role user_role NOT NULL DEFAULT 'user',
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Таблица для токенов сброса пароля
CREATE TABLE password_resets (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- Таблица для директорий (папок)
CREATE TABLE directories (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    parent_id INT,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES directories(id) ON DELETE CASCADE,
    UNIQUE (user_id, parent_id, name)
);

CREATE UNIQUE INDEX directories_parent_id_null_unique
ON directories (user_id, name)
WHERE parent_id IS NULL;

-- Таблица для файлов
CREATE TABLE files (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL, -- Владелец файла
    directory_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL UNIQUE,
    mime_type VARCHAR(100) NOT NULL,
    size BIGINT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (directory_id) REFERENCES directories(id) ON DELETE CASCADE,
    UNIQUE (directory_id, name)
);

-- Новая таблица для предоставления доступа к файлам
CREATE TABLE file_shares (
    id SERIAL PRIMARY KEY,
    file_id INT NOT NULL,
    user_id INT NOT NULL, -- Пользователь, которому предоставили доступ
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (file_id) REFERENCES files(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (file_id, user_id) -- Нельзя поделиться одним и тем же файлом дважды с одним пользователем
);


-- Триггерная функция для автоматического обновления поля updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
   NEW.updated_at = NOW(); 
   RETURN NEW;
END;
$$ language 'plpgsql';

-- Применяем триггер к таблицам
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_directories_updated_at BEFORE UPDATE ON directories FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_files_updated_at BEFORE UPDATE ON files FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
