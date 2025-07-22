CREATE TABLE user (
  UID VARCHAR(50) PRIMARY KEY,
  password VARCHAR(255) NOT NULL,
  fName VARCHAR(50),
  lName VARCHAR(50),
  jobDescription VARCHAR(100),
  viewPriveledgeYN CHAR(1),
  insertPriveledgeYN CHAR(1),
  updatePriveledgeYN CHAR(1),
  deletePriveledgeYN CHAR(1)
);
