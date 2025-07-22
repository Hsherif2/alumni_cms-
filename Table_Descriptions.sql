CREATE TABLE address (
    addressID INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
    alumniID INTEGER NOT NULL,
    address VARCHAR(50),
    city VARCHAR(50),
    state CHAR(2),
    zipCode VARCHAR(10),
    activeYN CHAR(1),
    primaryYN CHAR(1),
    FOREIGN KEY (alumniID) REFERENCES alumni(alumniID)
);

CREATE TABLE employment (
    EID INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
    alumniID INTEGER NOT NULL,
    company VARCHAR(50) NOT NULL,
    city VARCHAR(50),
    state CHAR(2),
    zip VARCHAR(10),
    jobTitle VARCHAR(20),
    startDate DATE,
    endDate DATE,
    currentYN CHAR(1),
    notes VARCHAR(100),
    FOREIGN KEY (alumniID) REFERENCES alumni(alumniID)
);

CREATE TABLE degree (
    degreeID INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
    alumniID INTEGER NOT NULL,
    major VARCHAR(50) NOT NULL,
    minor VARCHAR(50),
    graduationDT DATE,
    university VARCHAR(100),
    city VARCHAR(50),
    state CHAR(2),
    FOREIGN KEY (alumniID) REFERENCES alumni(alumniID)
);

CREATE TABLE skillset (
    SID INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
    alumniID INTEGER NOT NULL,
    skill VARCHAR(50) NOT NULL,
    proficiency VARCHAR(10),
    description VARCHAR(100),
    FOREIGN KEY (alumniID) REFERENCES alumni(alumniID)
);

CREATE TABLE donations (
    donationID INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
    alumniID INTEGER NOT NULL,
    donationAmt DECIMAL(11,2) NOT NULL,
    donationDT DATE NOT NULL,
    reason VARCHAR(200),
    description VARCHAR(200),
    FOREIGN KEY (alumniID) REFERENCES alumni(alumniID)
);




