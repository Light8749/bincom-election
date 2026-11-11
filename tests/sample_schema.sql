-- Local test fixture ONLY: same table/column names as bincom_test.sql with a few made-up rows.
-- On the real server, import the official bincom_test.sql instead.
CREATE TABLE lga (uniqueid INTEGER PRIMARY KEY AUTOINCREMENT, lga_id INT NOT NULL, lga_name VARCHAR(50) NOT NULL, state_id INT NOT NULL, lga_description TEXT, entered_by_user VARCHAR(50), date_entered DATETIME, user_ip_address VARCHAR(50));
CREATE TABLE ward (uniqueid INTEGER PRIMARY KEY AUTOINCREMENT, ward_id INT NOT NULL, ward_name VARCHAR(50) NOT NULL, lga_id INT NOT NULL, ward_description TEXT, entered_by_user VARCHAR(50), date_entered DATETIME, user_ip_address VARCHAR(50));
CREATE TABLE polling_unit (uniqueid INTEGER PRIMARY KEY AUTOINCREMENT, polling_unit_id INT NOT NULL, ward_id INT NOT NULL, lga_id INT NOT NULL, uniquewardid INT, polling_unit_number VARCHAR(50), polling_unit_name VARCHAR(50), polling_unit_description TEXT, lat VARCHAR(255), `long` VARCHAR(255), entered_by_user VARCHAR(50), date_entered DATETIME, user_ip_address VARCHAR(50));
CREATE TABLE party (id INTEGER PRIMARY KEY AUTOINCREMENT, partyid VARCHAR(11) NOT NULL, partyname VARCHAR(11) NOT NULL);
CREATE TABLE announced_pu_results (result_id INTEGER PRIMARY KEY AUTOINCREMENT, polling_unit_uniqueid VARCHAR(50) NOT NULL, party_abbreviation CHAR(4) NOT NULL, party_score INT NOT NULL, entered_by_user VARCHAR(50) NOT NULL, date_entered DATETIME NOT NULL, user_ip_address VARCHAR(50) NOT NULL);
CREATE TABLE announced_lga_results (result_id INTEGER PRIMARY KEY AUTOINCREMENT, lga_name VARCHAR(50) NOT NULL, party_abbreviation CHAR(4) NOT NULL, party_score INT NOT NULL, entered_by_user VARCHAR(50) NOT NULL, date_entered DATETIME NOT NULL, user_ip_address VARCHAR(50) NOT NULL);
INSERT INTO lga (lga_id,lga_name,state_id) VALUES (17,'Isoko South',25),(19,'Ndokwa East',25);
INSERT INTO ward (ward_id,ward_name,lga_id) VALUES (8,'Uzere',17),(9,'Ole',17),(8,'Ibrede',19);
INSERT INTO polling_unit (polling_unit_id,ward_id,lga_id,uniquewardid,polling_unit_number,polling_unit_name) VALUES
 (1,8,17,1,'DT1708001','Sapele Ward 8 PU'),(2,8,17,1,'DT1708002','Primary School Uzere'),(1,9,17,2,'DT1709001','Ole Town Hall'),(1,8,19,3,'DT1908001','Ibrede Market');
INSERT INTO party (partyid,partyname) VALUES ('PDP','PDP'),('DPP','DPP'),('ACN','ACN'),('PPA','PPA'),('CDC','CDC'),('JP','JP'),('ANPP','ANPP'),('LABOUR','LABOUR'),('CPP','CPP');
INSERT INTO announced_pu_results (polling_unit_uniqueid,party_abbreviation,party_score,entered_by_user,date_entered,user_ip_address) VALUES
 ('1','PDP',802,'Bose','2011-04-26 09:00:00','1.1.1.1'),('1','DPP',719,'Bose','2011-04-26 09:00:00','1.1.1.1'),('1','ACN',416,'Bose','2011-04-26 09:00:00','1.1.1.1'),
 ('3','PDP',100,'Ada','2011-04-26 09:00:00','1.1.1.1'),('3','ACN',50,'Ada','2011-04-26 09:00:00','1.1.1.1'),('4','PDP',999,'X','2011-04-26 09:00:00','1.1.1.1');
INSERT INTO announced_lga_results (lga_name,party_abbreviation,party_score,entered_by_user,date_entered,user_ip_address) VALUES
 ('17','PDP',905,'x','2011-04-26','1'),('17','DPP',719,'x','2011-04-26','1'),('17','ACN',466,'x','2011-04-26','1');
