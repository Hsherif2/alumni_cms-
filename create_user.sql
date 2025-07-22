
-- USER TABLE
CREATE TABLE user (
    UID VARCHAR(20) PRIMARY KEY NOT NULL,
    password VARCHAR(20) NOT NULL,
    fName VARCHAR(20) NOT NULL,
    lName VARCHAR(20) NOT NULL,
    jobDescription VARCHAR(50),
    viewPriveledgeYN CHAR(1),
    insertPriveledgeYN CHAR(1),
    updatePriveledgeYN CHAR(1),
    deletePriveledgeYN CHAR(1)
);
