# IU Hostel Reservation and Management System
A web-based application for hostel reservation and management at IU university. 
### **Author: Eric Clinton Appiah-Kubi**

## Deploying on a Local Host
### Install Dependencies
1. **[Apache 2.4.54](https://httpd.apache.org/docs/)** or later
2. **[PHP 8.1.10](https://www.php.net/downloads)** or later
3. **[MySQL Server 8.0.31](https://dev.mysql.com/downloads/mysql/)** or later
4. **[Composer](https://getcomposer.org/download/) Dependencies** 
5. **[Git](https://git-scm.com/downloads)**


You can deploy one of several apache deployment stacks such as:
1. **[XAMPP](http://www.apachefriends.org/en/xampp.html)**
2. **[WAMPServer](http://www.wampserver.com/)**
3. **[Bitnami WAMP Stack](http://bitnami.com/stack/wamp)**
4. **[AMPPS](http://www.ampps.com/)**
5. **[MAMP](http://www.mamp.info/en/index.html)**
6. **[LAMP](http://www.lamphowto.com/)**

These stacks combine **PHP, Apache, and MySQL server** installations in a single deployment.

### Clone the Project Repository

Clone the project to apache's document root folder, typically, **www** or **htdocs**.
The location of this folder depends on how you installed apache on your system. A quick Google search can be helpful.
```bash
git clone <repo> www
```
Navigate to the cloned folder.
```bash
cd www
```

### Install Dependencies
Install the required dependencies by running:
```bash
composer install
``` 

### Configure the Application
1. Rename the `.env.example` and `app/config/secrets-example.php` files to `.env` and `app/config/secrets.php` respectively and update the values to match your local environment.
2. Update the values in `app/config/config.php` file to match your local environment.

### Set up the Database
With MySQL server running, create the `iuhrms` database from a terminal window:

```bash
mysql -uroot -p
```
```sql
CREATE DATABASE IF NOT EXISTS iuhrms
```

Create the tables using the `001-iuhrms.sql` file located in the `sql-scripts` folder:

```bash
mysql -u root -p iuhrms < "sql-scripts/001-iuhrms.sql"
```
>**Note:** The above command assumes that you are running the command from the root directory of the project. If not, you will need to specify the full path to the sql file.

Populate the database
```bash
mysql -u root -p iuhrms < "sql-scripts/002-iuhrms.sql"
```

Alternatively, you can use a GUI application such as [MySQL Workbench](https://www.mysql.com/products/workbench/) or the web-based database manager [phpMyAdmin](https://www.phpmyadmin.net/) to connect to the Mysql server, create the database and run the sql files in sql-scripts folder to create the tables and populate the database.

### Test the Local Deployment
If you are using a standard XAMPP setup, use a web browser to navigate to **[http://localhost/iuhrms]()**

## Container Deployment Using Docker

### Install Dependencies
1. **[Docker](https://www.docker.com/get-started/)**
2. **[Git](https://git-scm.com/downloads)**

### Clone the Project Repository
Clone the project to a desired folder on your system.
```bash
git clone <repo> folder-name
```
Navigate to the cloned folder.
```bash
cd folder-name
```

### Configure the Application
1. Rename the `.env.example` and `app/config/secrets-example.php` files to `.env` and `app/config/secrets.php` respectively and update the values to match your local environment.
2. Update the values in `app/config/config.php` file to match your local environment.

To build the Docker images, navigate to the root directory of the project and run the following commands:
```bash
docker build -t iuhrms -f Dockerfile-apache .
docker build -t mysql -f Dockerfile-mysql . 
```
Alternatively, you can pull the Docker images from the Docker registry
```bash
docker pull ecakubi/iuhrms
docker pull ecakubi/mysql
```

Create a docker network:
```bash
docker network create docker-local
```

You may need to create a storage folder for bind mounting the mysql database folder. This folder will be used to persist the database files even after the container is destroyed.
```bash
mkdir storage
```

Spin up docker containers from the images:
```bash
docker run -d -p 8000:80 -v %cd%:/var/www/html --network docker-local --name iuhrms ecakubi/iuhrms
docker run -d -p 3309:3306 -v %cd%/storage:/var/lib/mysql -v %cd%/sql-scripts:/docker-entrypoint-initdb.d --network docker-local --env-file .env --name mysql mysql
```
>**Note:**
> 1. The volume bind mount to /var/www/html is not needed for running the application. It is only needed if you want to make changes to the application and test it in the container.
> 2. Take note of the storage folder bind mount. This is the folder created in the previous step. You may ignore it if you did not create the folder or persistence is not required.
> 3. On Linux, %cd% may not work. In this case, use the `pwd` command to get the current directory. For example:
```bash
docker run -d -p 8000:80 -v $(pwd):/var/www/html --network docker-local --name iuhrms ecakubi/iuhrms
```
You can access the application on the host machine by visiting http://localhost:8000 in a web browser.