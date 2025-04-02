

-- Admin Table
CREATE TABLE Admin (
    adminID INT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    firstName VARCHAR(100),
    lastName VARCHAR(100),
    role VARCHAR(50) NOT NULL, -- e.g., 'Super Admin', 'Content Manager', 'Moderator'
    permissions VARCHAR(255), -- Can store JSON or comma-separated list of permissions
    isActive BOOLEAN DEFAULT TRUE,
    lastLogin TIMESTAMP,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Example Insert Statement
INSERT INTO Admin VALUES
(1, 'root_admin', 'admin@gameplatform.com', 'secureAdminPassword123', 'System', 'Administrator', 'Super Admin', 'full_access', TRUE, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 'content_manager', 'content@gameplatform.com', 'contentPass456', 'Content', 'Manager', 'Content Manager', 'moderate_content,edit_games', TRUE, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, 'support_admin', 'support@gameplatform.com', 'supportPass789', 'Support', 'Administrator', 'Support', 'user_management,resolve_issues', TRUE, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);





-- User Table
CREATE TABLE User (
    userID INT PRIMARY KEY,
    name CHAR(100) NOT NULL,
    surname CHAR(100) NOT NULL,
    email CHAR(255) NOT NULL,
    password CHAR(255) NOT NULL,
    numberOfGamesInLibrary INT NOT NULL
);

-- Game Table
CREATE TABLE Game (
    gameID INT PRIMARY KEY,
    gameName CHAR(255) NOT NULL,
    price REAL NOT NULL,
    likeCount INT NOT NULL
);

-- Subscription Table
CREATE TABLE Subscription (
    subscriptionID INT PRIMARY KEY
);

-- User_Subscription Table
CREATE TABLE User_Subscription (
    userID INT UNIQUE NOT NULL,
    subscriptionID INT NOT NULL,
    since DATE NOT NULL,
    PRIMARY KEY (userID, subscriptionID),
    FOREIGN KEY (userID) REFERENCES User(userID) ON DELETE CASCADE,
    FOREIGN KEY (subscriptionID) REFERENCES Subscription(subscriptionID) ON DELETE CASCADE
);

-- Platform Table
CREATE TABLE Platform (
    platformName CHAR(100) PRIMARY KEY
);

-- Game_Platform Table
CREATE TABLE Game_Platform (
    gameID INT NOT NULL,
    platformName CHAR(100) NOT NULL,
    PRIMARY KEY (gameID, platformName),
    FOREIGN KEY (gameID) REFERENCES Game(gameID) ON DELETE CASCADE,
    FOREIGN KEY (platformName) REFERENCES Platform(platformName) ON DELETE CASCADE
);

-- Discount Table
CREATE TABLE Discount (
    discountID INT PRIMARY KEY,
    discountPercentage DECIMAL(5,2) NOT NULL,
    startDate DATE NOT NULL,
    endDate DATE NOT NULL
);

-- Has_Discount Table
CREATE TABLE Has_Discount (
    gameID INT,
    discountID INT,
    PRIMARY KEY (gameID, discountID),
    FOREIGN KEY (gameID) REFERENCES Game(gameID) ON DELETE CASCADE,
    FOREIGN KEY (discountID) REFERENCES Discount(discountID) ON DELETE CASCADE
);

-- Review Table
CREATE TABLE Review (
    reviewID INT PRIMARY KEY,
    reviewText CHAR(255) NOT NULL
);

-- Writing_Reviews Table
CREATE TABLE Writing_Reviews (
    userID INT NOT NULL,
    reviewID INT NOT NULL,
    PRIMARY KEY (userID, reviewID),
    FOREIGN KEY (userID) REFERENCES User(userID) ON DELETE CASCADE,
    FOREIGN KEY (reviewID) REFERENCES Review(reviewID) ON DELETE CASCADE
);

-- Displaying_Reviews Table
CREATE TABLE Displaying_Reviews (
    gameID INT NOT NULL,
    reviewID INT NOT NULL,
    PRIMARY KEY (gameID, reviewID),
    FOREIGN KEY (gameID) REFERENCES Game(gameID) ON DELETE CASCADE,
    FOREIGN KEY (reviewID) REFERENCES Review(reviewID) ON DELETE CASCADE
);

-- Liked Table
CREATE TABLE Liked (
    userID INT NOT NULL,
    gameID INT NOT NULL,
    PRIMARY KEY (userID, gameID),
    FOREIGN KEY (userID) REFERENCES User(userID) ON DELETE CASCADE,
    FOREIGN KEY (gameID) REFERENCES Game(gameID) ON DELETE CASCADE
);

-- Library Table
CREATE TABLE Library (
    userID INT,
    gameID INT,
    dateAdded DATE NOT NULL,
    PRIMARY KEY (userID, gameID),
    FOREIGN KEY (userID) REFERENCES User(userID) ON DELETE CASCADE,
    FOREIGN KEY (gameID) REFERENCES Game(gameID) ON DELETE CASCADE
);

-- Genre Table
CREATE TABLE Genre (
    genreName VARCHAR(255) NOT NULL PRIMARY KEY
);

-- Game_Genre Table
CREATE TABLE Game_Genre (
    gameID INT,
    genreName VARCHAR(255),
    PRIMARY KEY (gameID, genreName),
    FOREIGN KEY (gameID) REFERENCES Game(gameID) ON DELETE CASCADE,
    FOREIGN KEY (genreName) REFERENCES Genre(genreName) ON DELETE CASCADE
);

-- Award Table
CREATE TABLE Award (
    awardName CHAR(255) NOT NULL,
    winningYear INT NOT NULL,
    category CHAR(255) NOT NULL,
    PRIMARY KEY (awardName, winningYear)
);

-- Game_Award Table
CREATE TABLE Game_Award (
    gameID INT NOT NULL,
    awardName CHAR(255) NOT NULL,
    winningYear INT NOT NULL,
    PRIMARY KEY (gameID, awardName, winningYear),
    FOREIGN KEY (gameID) REFERENCES Game(gameID) ON DELETE CASCADE,
    FOREIGN KEY (awardName, winningYear) REFERENCES Award(awardName, winningYear) ON DELETE CASCADE
);

-- Brand Table
CREATE TABLE Brand (
    brandName VARCHAR(255) PRIMARY KEY,
    headquarter VARCHAR(255) NOT NULL
);

-- Publisher Table
CREATE TABLE Publisher (
    publisherName VARCHAR(255) PRIMARY KEY
);

-- Developer Table
CREATE TABLE Developer (
    developerID INT PRIMARY KEY,
    devName VARCHAR(255) NOT NULL UNIQUE
);

-- GameEngine Table
CREATE TABLE GameEngine (
    engineID INT PRIMARY KEY,
    engineName VARCHAR(255) NOT NULL UNIQUE,
    version VARCHAR(50) NOT NULL,
    releaseDate DATE
);

-- PublishedBy Table
CREATE TABLE PublishedBy (
    gameID INT,
    publisherName VARCHAR(255),
    PRIMARY KEY (gameID, publisherName),
    FOREIGN KEY (gameID) REFERENCES Game(gameID) ON DELETE CASCADE,
    FOREIGN KEY (publisherName) REFERENCES Publisher(publisherName) ON DELETE CASCADE
);

-- DevelopedBy Table
CREATE TABLE DevelopedBy (
    gameID INT,
    developerID INT,
    PRIMARY KEY (gameID, developerID),
    FOREIGN KEY (gameID) REFERENCES Game(gameID) ON DELETE CASCADE,
    FOREIGN KEY (developerID) REFERENCES Developer(developerID) ON DELETE CASCADE
);

-- DeveloperUsesEngine Table
CREATE TABLE DeveloperUsesEngine (
    developerID INT,
    engineID INT,
    PRIMARY KEY (developerID, engineID),
    FOREIGN KEY (developerID) REFERENCES Developer(developerID) ON DELETE CASCADE,
    FOREIGN KEY (engineID) REFERENCES GameEngine(engineID) ON DELETE CASCADE
);

-- WorksWith Table
CREATE TABLE WorksWith (
    publisherName VARCHAR(255),
    developerID INT,
    contractDate DATE NOT NULL,
    PRIMARY KEY (publisherName, developerID),
    FOREIGN KEY (publisherName) REFERENCES Publisher(publisherName) ON DELETE CASCADE,
    FOREIGN KEY (developerID) REFERENCES Developer(developerID) ON DELETE CASCADE
);

-- PublishesThrough Table
CREATE TABLE PublishesThrough (
    brandName VARCHAR(255),
    publisherName VARCHAR(255),
    PRIMARY KEY (brandName, publisherName),
    FOREIGN KEY (brandName) REFERENCES Brand(brandName) ON DELETE CASCADE,
    FOREIGN KEY (publisherName) REFERENCES Publisher(publisherName) ON DELETE CASCADE
);

-- Insert Statements (Kept as in the original document)
INSERT INTO User VALUES
(1, 'Alice', 'Smith', 'alice@email.com', 'password123', 5),
(2, 'Bob', 'Johnson', 'bob@email.com', 'securePass', 3),
(3, 'Charlie', 'Brown', 'charlie@email.com', 'charlie123', 7),
(4, 'David', 'Williams', 'david@email.com', 'davidPass', 2),
(5, 'Eve', 'Davis', 'eve@email.com', 'evePass!', 6),
(6, 'Frank', 'Miller', 'frank@email.com', 'frank123', 4),
(7, 'Grace', 'Wilson', 'grace@email.com', 'gracePass', 5),
(8, 'Hank', 'Moore', 'hank@email.com', 'hankSecure', 3),
(9, 'Ivy', 'Taylor', 'ivy@email.com', 'ivyPass', 4),
(10, 'Jack', 'Anderson', 'jack@email.com', 'jackPass!', 6);

INSERT INTO Game VALUES
(1, 'Cyberpunk 2077', 59.99, 2000),
(2, 'The Witcher 3', 39.99, 5000),
(3, 'Minecraft', 29.99, 8000),
(4, 'Elden Ring', 69.99, 3500),
(5, 'FIFA 24', 49.99, 4000),
(6, 'Call of Duty: MW3', 79.99, 3200),
(7, 'GTA V', 29.99, 10000),
(8, 'Assassin''s Creed Valhalla', 59.99, 2700),
(9, 'Red Dead Redemption 2', 49.99, 6000),
(10, 'Hogwarts Legacy', 69.99, 3300);

-- (Rest of the insert statements remain the same as in the original document)
INSERT INTO Platform VALUES
('PC'), ('PlayStation 5'), ('Xbox Series X'), ('Nintendo Switch'), 
('PlayStation 4'), ('Xbox One'), ('Mobile'), ('Stadia'), 
('Steam Deck'), ('MacOS');

INSERT INTO Game_Platform VALUES
(1, 'PC'), (1, 'PlayStation 5'), (2, 'PC'), (3, 'Xbox Series X'), 
(4, 'Nintendo Switch'), (5, 'PlayStation 4'), (6, 'Xbox One'), 
(7, 'Mobile'), (8, 'Stadia'), (9, 'Steam Deck'), (10, 'MacOS');

-- (Continue with the rest of the INSERT statements from the original document)
INSERT INTO Subscription VALUES
(1), (2), (3), (4), (5), (6), (7), (8), (9), (10);

INSERT INTO User_Subscription VALUES
(1, 1, '2024-01-01'), (2, 2, '2024-02-01'), (3, 3, '2024-03-01'), 
(4, 4, '2024-04-01'), (5, 5, '2024-05-01'), (6, 6, '2024-06-01'), 
(7, 7, '2024-07-01'), (8, 8, '2024-08-01'), (9, 9, '2024-09-01'), 
(10, 10, '2024-10-01');

-- Insert Genres
INSERT INTO Genre (genreName) VALUES
('RPG'),
('Action'),
('Adventure'),
('Open World'),
('Shooter'),
('Sports'),
('Simulation'),
('Strategy'),
('Racing'),
('Puzzle'),
('Horror'),
('MMORPG'),
('Survival'),
('Fighting'),
('Platformer');

INSERT INTO Publisher (publisherName) VALUES
('CD Projekt Red'),
('Microsoft Studios'),
('EA Sports'),
('Activision'),
('Rockstar Games'),
('Ubisoft'),
('Bandai Namco'),
('Warner Bros');

-- Link Games to Genres
INSERT INTO Game_Genre (gameID, genreName) VALUES
(1, 'RPG'),
(1, 'Action'),
(1, 'Open World'),
(2, 'RPG'),
(2, 'Adventure'),
(3, 'Simulation'),
(4, 'Action'),
(4, 'Adventure'),
(5, 'Sports'),
(6, 'Shooter'),
(7, 'Action'),
(7, 'Open World'),
(8, 'Action'),
(8, 'Adventure'),
(9, 'Action'),
(9, 'Open World'),
(10, 'RPG');

-- Link Games to Publishers
INSERT INTO PublishedBy (gameID, publisherName) VALUES
(1, 'CD Projekt Red'),
(2, 'CD Projekt Red'),
(3, 'Microsoft Studios'),
(4, 'Bandai Namco'),
(5, 'EA Sports'),
(6, 'Activision'),
(7, 'Rockstar Games'),
(8, 'Ubisoft'),
(9, 'Rockstar Games'),
(10, 'Warner Bros');



