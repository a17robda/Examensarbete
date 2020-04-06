DROP DATABASE IF EXISTS exjobb;
CREATE DATABASE exjobb;
USE exjobb;

CREATE TABLE jsontable(
    id int AUTO_INCREMENT,
    nyckelkod JSON,
    PRIMARY KEY(id)
);


