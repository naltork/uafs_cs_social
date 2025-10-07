# uafs_cs_social
### step1: Install mariaDB
### Step2: create DB that name as "uafs_cs_social"
### step3: Create a user which will help our code base to interact with mariaDB
CREATE USER 'uafs_app'@'localhost' IDENTIFIED BY 'insert_your_password';
GRANT ALL PRIVILEGES ON uafs_cs_social.* TO 'uafs_app'@'localhost';
FLUSH PRIVILEGES;
### Step4: Command to gerrate hash password
php -r 'echo password_hash("Passw0rd!", PASSWORD_DEFAULT), PHP_EOL;'

### Step5:  Commands used to run the queries for generating and seeding the data in the uafs_cs_social mariadb.
mariadb -u root -p uafs_cs_social < database/migrations/001_create_users.sql
mariadb -u root -p uafs_cs_social < database/seeds/001_seed_users.sql

