-- CREATE DATABASE music_app;

-- USE music_app;

CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    passwordHash VARCHAR(255) NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    avatar_url VARCHAR(255),
    last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    role VARCHAR(20) DEFAULT 'user',
    bio TEXT
);

CREATE TABLE userTokens (
    token_id INT PRIMARY KEY AUTO_INCREMENT,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    device_info VARCHAR(255),
    revoked BOOLEAN DEFAULT FALSE,
-- FOREIGN
    user_id INT
);
ALTER TABLE userTokens ADD FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE;


CREATE TABLE searchHistory (
    search_id INT PRIMARY KEY AUTO_INCREMENT,
    query_text VARCHAR(255) NOT NULL,
    searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
-- FOREIGN
    user_id INT
    
);
ALTER TABLE searchHistory ADD FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE;

CREATE TABLE playlists (
    playlist_id INT PRIMARY KEY AUTO_INCREMENT,
    playlist_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_public BOOLEAN DEFAULT FALSE
);


CREATE TABLE userPlaylists (
-- fOREIGN
    user_id INT, 
    playlist_id INT,
--
    shared_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    role VARCHAR(20) DEFAULT 'owner',

    PRIMARY KEY (user_id, playlist_id)
);
ALTER TABLE userPlaylists ADD FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE;
ALTER TABLE userPlaylists ADD FOREIGN KEY (playlist_id) REFERENCES playlists(playlist_id) ON DELETE CASCADE;

CREATE TABLE artists (
    artist_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL, 
    avatar_url VARCHAR(255),
    description TEXT,
    country VARCHAR(100)

);

CREATE TABLE albums (
    album_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    release_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cover_url VARCHAR(255),

    --foreign keys
    artist_id INT
);
ALTER TABLE albums ADD FOREIGN KEY (artist_id) REFERENCES artists(artist_id) ON DELETE CASCADE;




CREATE TABLE songs (
    song_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    release_date DATE,
    duration_seconds INT,
    total_plays INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_explicit BOOLEAN DEFAULT FALSE,
    cover_url VARCHAR(255),

    audio_url VARCHAR(255) NOT NULL,
    --foreign keys
    album_id INT NULL
);
ALTER TABLE songs ADD FOREIGN KEY (album_id) REFERENCES albums(album_id) ON DELETE SET NULL;

CREATE TABLE songArtists (
    song_id INT,
    artist_id INT,
    role VARCHAR(100),
    PRIMARY KEY (song_id, artist_id)
);
ALTER TABLE songArtists ADD FOREIGN KEY (song_id) REFERENCES songs(song_id) ON DELETE CASCADE;
ALTER TABLE songArtists ADD FOREIGN KEY (artist_id) REFERENCES artists(artist_id) ON DELETE CASCADE;

CREATE TABLE Genres (
    genre_id INT PRIMARY KEY AUTO_INCREMENT,
    genre_name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE songGenres (
    song_id INT,
    genre_id INT,
    PRIMARY KEY (song_id, genre_id)
);
ALTER TABLE songGenres ADD FOREIGN KEY (song_id) REFERENCES songs(song_id) ON DELETE CASCADE;
ALTER TABLE songGenres ADD FOREIGN KEY (genre_id) REFERENCES Genres(genre_id) ON DELETE CASCADE;

CREATE TABLE playlistSongs (
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    track_order INT,
    --foreign keys
    playlist_id INT,
    song_id INT,
    --
    PRIMARY KEY (playlist_id, song_id)
);
ALTER TABLE playlistSongs ADD FOREIGN KEY (playlist_id) REFERENCES playlists(playlist_id) ON DELETE CASCADE;
ALTER TABLE playlistSongs ADD FOREIGN KEY (song_id) REFERENCES songs(song_id) ON DELETE CASCADE;

-- CREATE TABLE userListenSongs (
--     time INT AUTO_INCREMENT,
--     listened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     listened_seconds INT,
--     --foreign keys
--     user_id INT,
--     song_id INT,
--     --
--     PRIMARY KEY (user_id, song_id, time)
-- );
-- ALTER TABLE userListenSongs ADD FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE;
-- ALTER TABLE userListenSongs ADD FOREIGN KEY (song_id) REFERENCES songs(song_id) ON DELETE CASCADE;

CREATE TABLE listen_to(
    listen_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    song_id INT,
    listened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    listened_seconds INT
);
ALTER TABLE listen_to ADD FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE;
ALTER TABLE listen_to ADD FOREIGN KEY (song_id) REFERENCES songs(song_id) ON DELETE CASCADE;



CREATE TABLE userCommentSongs (
    comment_id INT PRIMARY KEY AUTO_INCREMENT,
    comment_text TEXT NOT NULL,
    commented_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    commented_parent_id INT,
    --foreign keys
    user_id INT,
    song_id INT
    --
);
ALTER TABLE userCommentSongs ADD FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE;
ALTER TABLE userCommentSongs ADD FOREIGN KEY (song_id) REFERENCES songs(song_id) ON DELETE CASCADE;
ALTER TABLE userCommentSongs ADD FOREIGN KEY (commented_parent_id) REFERENCES userCommentSongs(comment_id) ON DELETE CASCADE;

CREATE TABLE userLikeSongs (
    liked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    --foreign keys
    user_id INT,
    song_id INT,
    --
    PRIMARY KEY (user_id, song_id)
);
ALTER TABLE userLikeSongs ADD FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE;
ALTER TABLE userLikeSongs ADD FOREIGN KEY (song_id) REFERENCES songs(song_id) ON DELETE CASCADE;

CREATE TABLE userFollowArtists (
    followed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    --foreign keys
    user_id INT,
    artist_id INT,
    --
    PRIMARY KEY (user_id, artist_id)
);
ALTER TABLE userFollowArtists ADD FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE;
ALTER TABLE userFollowArtists ADD FOREIGN KEY (artist_id) REFERENCES artists(artist_id) ON DELETE CASCADE;

CREATE TABLE userLikeAlbums (
    liked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    --foreign keys
    user_id INT,
    album_id INT,
    --
    PRIMARY KEY (user_id, album_id)
);
ALTER TABLE userLikeAlbums ADD FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE;
ALTER TABLE userLikeAlbums ADD FOREIGN KEY (album_id) REFERENCES albums(album_id) ON DELETE CASCADE;

CREATE TABLE userRecommendations (
    recommended_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    score FLOAT,
    reason TEXT,
    --foreign keys
    user_id INT,
    song_id INT,
    --
    PRIMARY KEY (user_id, song_id)
);
ALTER TABLE userRecommendations ADD FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE;
ALTER TABLE userRecommendations ADD FOREIGN KEY (song_id) REFERENCES songs(song_id) ON DELETE CASCADE;

CREATE TABLE plan (
    plan_id INT PRIMARY KEY AUTO_INCREMENT,
    plan_name VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    price DECIMAL(10, 2) NOT NULL,
    billing_cycle VARCHAR(50) NOT NULL,
    description TEXT
);

CREATE TABLE subscriptions (
    subscription_id INT PRIMARY KEY AUTO_INCREMENT,
    subscription_type VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    --foreign keys
    user_id INT,
    plan_id INT
);
ALTER TABLE subscriptions ADD FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE;
ALTER TABLE subscriptions ADD FOREIGN KEY (plan_id) REFERENCES plan(plan_id) ON DELETE SET NULL;

CREATE TABLE payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    amount DECIMAL(10, 2) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_method VARCHAR(50) NOT NULL,
    status VARCHAR(50) NOT NULL,
    --foreign keys
    subscription_id INT NULL
);
ALTER TABLE payments ADD FOREIGN KEY (subscription_id) REFERENCES subscriptions(subscription_id) ON DELETE SET NULL;