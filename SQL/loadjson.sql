DROP DATABASE IF EXISTS exjobb;
CREATE DATABASE exjobb;
USE exjobb;

CREATE TABLE jsontable(
    id int AUTO_INCREMENT,
    jsonrow JSON,
    PRIMARY KEY(id)
);


