/**
*  Jacob Landowski
*  3-1-18
*/

CREATE TABLE User
(
    id            INT          NOT NULL AUTO_INCREMENT,
    username      VARCHAR(20),
    email         VARCHAR(50),
    password      VARCHAR(255) NOT NULL,
    num_threads   INT          NOT NULL DEFAULT 0,
    num_posts     INT          NOT NULL DEFAULT 0,
    PRIMARY KEY(id),
    UNIQUE(username),
    UNIQUE(email)

) ENGINE=InnoDB;

/* CHECK FOR THIS USER ON LOGINS */
INSERT INTO User(username, email, password) VALUES('USER_GRAVEYARD', 'unused', 'unused');

/* CREATE THREAD GRAVEYARD WHEN USER IS MADE */
CREATE TABLE Thread
(
    id            INT         NOT NULL AUTO_INCREMENT,
    owner         INT         NOT NULL,
    owner_name    VARCHAR(20) NOT NULL,
    title         VARCHAR(40),
    replies       INT         NOT NULL DEFAULT 0,
    views         INT         NOT NULL DEFAULT 0,
    created       TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_reply    TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    bot_generated TINYINT     NOT NULL DEFAULT 0,
    parsed        TINYINT     NOT NULL DEFAULT 0,
    PRIMARY KEY(id),
    FOREIGN KEY(owner)      REFERENCES User(id),
    FOREIGN KEY(owner_name) REFERENCES User(username), 
    UNIQUE(title)

) ENGINE=InnoDB;

CREATE TABLE Thread_User_Views
(
    id     INT NOT NULL AUTO_INCREMENT,
    thread INT NOT NULL,
    user   INT NOT NULL,
    PRIMARY KEY(id),
    FOREIGN KEY(thread) REFERENCES Thread(id) ON DELETE CASCADE,
    FOREIGN KEY(user)   REFERENCES User(id)   ON DELETE CASCADE,
    UNIQUE(thread, user)
) ENGINE=InnoDB;

CREATE TABLE Post
(
    id            INT         NOT NULL AUTO_INCREMENT,
    thread        INT         NOT NULL,
    owner         INT         NOT NULL,
    owner_name    VARCHAR(20) NOT NULL,
    created       TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    content       TEXT        NOT NULL,
    last_edit     TIMESTAMP,
    bot_generated TINYINT     NOT NULL DEFAULT 0,
    parsed        TINYINT     NOT NULL DEFAULT 0,
    is_root_post  TINYINT     NOT NULL DEFAULT 0,
    PRIMARY KEY(id),
    FOREIGN KEY(thread)     REFERENCES Thread(id) ON DELETE CASCADE,
    FOREIGN KEY(owner)      REFERENCES User(id),
    FOREIGN KEY(owner_name) REFERENCES User(username)

) ENGINE=InnoDB;

CREATE TABLE TextMap
(
    id       INT        NOT NULL AUTO_INCREMENT,
    map_data MEDIUMBLOB NOT NULL,
    owner    INT        NOT NULL,
    was_used TINYINT    NOT NULL DEFAULT 0,
    PRIMARY KEY(id),
    FOREIGN KEY(owner) REFERENCES User(id) ON DELETE CASCADE

) ENGINE=InnoDB;