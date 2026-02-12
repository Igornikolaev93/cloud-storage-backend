-- Adminer 4.8.1 PostgreSQL 16.1 (Debian 16.1-1.pgdg120+1) dump

DROP TABLE IF EXISTS "directories";
DROP SEQUENCE IF EXISTS directories_id_seq;
CREATE SEQUENCE directories_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."directories" (
    "id" integer DEFAULT nextval('directories_id_seq') NOT NULL,
    "user_id" integer NOT NULL,
    "parent_id" integer,
    "name" character varying(255) NOT NULL,
    "created_at" timestamp DEFAULT now() NOT NULL,
    "updated_at" timestamp DEFAULT now() NOT NULL,
    CONSTRAINT "directories_pkey" PRIMARY KEY ("id"),
    CONSTRAINT "directories_user_id_fkey" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE NOT DEFERRABLE,
    CONSTRAINT "directories_parent_id_fkey" FOREIGN KEY (parent_id) REFERENCES directories(id) ON DELETE CASCADE NOT DEFERRABLE
) WITH (oids = false);


DROP TABLE IF EXISTS "files";
DROP SEQUENCE IF EXISTS files_id_seq;
CREATE SEQUENCE files_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."files" (
    "id" integer DEFAULT nextval('files_id_seq') NOT NULL,
    "user_id" integer NOT NULL,
    "parent_id" integer,
    "is_folder" boolean DEFAULT false NOT NULL,
    "name" character varying(255) NOT NULL,
    "path" character varying(255),
    "size" bigint,
    "type" character varying(255),
    "is_public" boolean DEFAULT false NOT NULL,
    "created_at" timestamp DEFAULT now() NOT NULL,
    "updated_at" timestamp DEFAULT now() NOT NULL,
    "downloads" integer DEFAULT '0' NOT NULL,
    CONSTRAINT "files_pkey" PRIMARY KEY ("id"),
    CONSTRAINT "files_user_id_fkey" FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE NOT DEFERRABLE
) WITH (oids = false);


DROP TABLE IF EXISTS "password_resets";
DROP SEQUENCE IF EXISTS password_resets_id_seq;
CREATE SEQUENCE password_resets_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."password_resets" (
    "id" integer DEFAULT nextval('password_resets_id_seq') NOT NULL,
    "email" character varying(255) NOT NULL,
    "token" character varying(255) NOT NULL,
    "created_at" timestamp DEFAULT now() NOT NULL,
    "expires_at" timestamp NOT NULL,
    CONSTRAINT "password_resets_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "shares";
DROP SEQUENCE IF EXISTS shares_id_seq;
CREATE SEQUENCE shares_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."shares" (
    "id" integer DEFAULT nextval('shares_id_seq') NOT NULL,
    "file_id" integer NOT NULL,
    "token" character varying(255) NOT NULL,
    "created_at" timestamp DEFAULT now() NOT NULL,
    "expires_at" timestamp,
    CONSTRAINT "shares_pkey" PRIMARY KEY ("id"),
    CONSTRAINT "shares_file_id_fkey" FOREIGN KEY (file_id) REFERENCES files(id) ON DELETE CASCADE NOT DEFERRABLE
) WITH (oids = false);


DROP TABLE IF EXISTS "users";
DROP SEQUENCE IF EXISTS users_id_seq;
CREATE SEQUENCE users_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."users" (
    "id" integer DEFAULT nextval('users_id_seq') NOT NULL,
    "username" character varying(50) NOT NULL,
    "email" character varying(255) NOT NULL,
    "password_hash" character varying(255) NOT NULL,
    "role" character varying(20) DEFAULT 'user' NOT NULL,
    "created_at" timestamp DEFAULT now() NOT NULL,
    "updated_at" timestamp DEFAULT now() NOT NULL,
    "is_active" boolean DEFAULT true NOT NULL,
    CONSTRAINT "users_email_key" UNIQUE ("email"),
    CONSTRAINT "users_pkey" PRIMARY KEY ("id"),
    CONSTRAINT "users_username_key" UNIQUE ("username")
) WITH (oids = false);
