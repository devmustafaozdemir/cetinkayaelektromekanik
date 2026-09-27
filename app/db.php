<?php
declare(strict_types=1);

function db(): PDO
{
    global $__pdo;
    return $__pdo;
}

function db_init(string $path): void
{
    global $__pdo;
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0775, true);
    }
    $fresh = !is_file($path);
    $__pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $__pdo->exec('PRAGMA foreign_keys = ON; PRAGMA journal_mode = WAL;');
    // Timestamps come from PHP so they always follow the app timezone (Europe/Istanbul),
    // regardless of the server's OS timezone that SQLite's 'localtime' would use.
    $now = fn() => date('Y-m-d H:i:s');
    if (method_exists($__pdo, 'createFunction')) {
        $__pdo->createFunction('now_tr', $now, 0);
    } else {
        $__pdo->sqliteCreateFunction('now_tr', $now, 0);
    }
    db_migrate();
    if ($fresh) {
        require APP . '/seed.php';
        db_seed();
    }
}

function db_migrate(): void
{
    db()->exec(<<<SQL
CREATE TABLE IF NOT EXISTS settings (
    key   TEXT PRIMARY KEY,
    value TEXT NOT NULL DEFAULT ''
);
CREATE TABLE IF NOT EXISTS users (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    username      TEXT NOT NULL UNIQUE,
    name          TEXT NOT NULL DEFAULT '',
    password_hash TEXT NOT NULL,
    last_login    TEXT,
    created_at    TEXT NOT NULL DEFAULT (datetime('now','localtime'))
);
CREATE TABLE IF NOT EXISTS login_attempts (
    ip TEXT NOT NULL,
    at INTEGER NOT NULL
);
CREATE TABLE IF NOT EXISTS categories (
    id   INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE
);
CREATE TABLE IF NOT EXISTS posts (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    title        TEXT NOT NULL,
    slug         TEXT NOT NULL UNIQUE,
    excerpt      TEXT NOT NULL DEFAULT '',
    content      TEXT NOT NULL DEFAULT '',
    cover        TEXT NOT NULL DEFAULT '',
    category_id  INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    status       TEXT NOT NULL DEFAULT 'draft',
    featured     INTEGER NOT NULL DEFAULT 0,
    views        INTEGER NOT NULL DEFAULT 0,
    meta_title   TEXT NOT NULL DEFAULT '',
    meta_desc    TEXT NOT NULL DEFAULT '',
    published_at TEXT,
    created_at   TEXT NOT NULL DEFAULT (datetime('now','localtime')),
    updated_at   TEXT NOT NULL DEFAULT (datetime('now','localtime'))
);
CREATE INDEX IF NOT EXISTS idx_posts_status ON posts(status, published_at);
CREATE TABLE IF NOT EXISTS services (
    id      INTEGER PRIMARY KEY AUTOINCREMENT,
    title   TEXT NOT NULL,
    slug    TEXT NOT NULL UNIQUE,
    icon    TEXT NOT NULL DEFAULT 'wrench',
    summary TEXT NOT NULL DEFAULT '',
    content TEXT NOT NULL DEFAULT '',
    sort    INTEGER NOT NULL DEFAULT 0,
    active  INTEGER NOT NULL DEFAULT 1
);
CREATE TABLE IF NOT EXISTS faqs (
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    question TEXT NOT NULL,
    answer   TEXT NOT NULL,
    sort     INTEGER NOT NULL DEFAULT 0,
    active   INTEGER NOT NULL DEFAULT 1
);
CREATE TABLE IF NOT EXISTS service_requests (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    code       TEXT NOT NULL UNIQUE,
    name       TEXT NOT NULL,
    phone      TEXT NOT NULL,
    email      TEXT NOT NULL DEFAULT '',
    company    TEXT NOT NULL DEFAULT '',
    brand      TEXT NOT NULL DEFAULT '',
    device     TEXT NOT NULL DEFAULT '',
    model      TEXT NOT NULL DEFAULT '',
    warranty   INTEGER NOT NULL DEFAULT 0,
    issue      TEXT NOT NULL DEFAULT '',
    status     TEXT NOT NULL DEFAULT 'received',
    admin_note TEXT NOT NULL DEFAULT '',
    ip         TEXT NOT NULL DEFAULT '',
    created_at TEXT NOT NULL DEFAULT (datetime('now','localtime')),
    updated_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
);
CREATE TABLE IF NOT EXISTS request_log (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    request_id INTEGER NOT NULL REFERENCES service_requests(id) ON DELETE CASCADE,
    status     TEXT NOT NULL,
    note       TEXT NOT NULL DEFAULT '',
    created_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
);
CREATE TABLE IF NOT EXISTS messages (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT NOT NULL,
    email      TEXT NOT NULL DEFAULT '',
    phone      TEXT NOT NULL DEFAULT '',
    subject    TEXT NOT NULL DEFAULT '',
    message    TEXT NOT NULL,
    is_read    INTEGER NOT NULL DEFAULT 0,
    ip         TEXT NOT NULL DEFAULT '',
    created_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
);
SQL);
}

function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function q_one(string $sql, array $params = []): ?array
{
    $row = q($sql, $params)->fetch();
    return $row === false ? null : $row;
}

function q_all(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

function q_val(string $sql, array $params = [])
{
    $v = q($sql, $params)->fetchColumn();
    return $v === false ? null : $v;
}
