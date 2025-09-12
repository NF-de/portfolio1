    -- Script SQL pour créer les tables `pages` et `contenu`

    -- Création de la table `pages`
    CREATE TABLE IF NOT EXISTS pages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titre VARCHAR(255) NOT NULL,
        id_parent INTEGER,
        FOREIGN KEY (id_parent) REFERENCES pages(id)
    );

    -- Création de la table `contenu`
    CREATE TABLE IF NOT EXISTS contenu (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titre VARCHAR(255) NOT NULL,
        paragraphe TEXT,
        images VARCHAR(255),
        page_id INTEGER,
        FOREIGN KEY (page_id) REFERENCES pages(id)
    );

    CREATE TABLE IF NOT EXISTS blogpost (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT,
        message TEXT
    );

    CREATE TABLE IF NOT EXISTS login (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL
);