# IU Hostel Reservation and Management System (IUHRMS)
A web-based application for hostel reservation and management at IU International University of Applied Sciences. 
### **Author: Eric Clinton Appiah-Kubi**


## Azure Deployment
The application has been deployed on Azure Container Instances and can be accessed at https://iuhrms.jomascowaves.com.
Please note that this deployment is for demonstration purposes only and may not be available at all times.
Look at the [Instructions.md](Instructions.md) file for more information on how to try out the application.


## Deploying on a Local Apache + PHP + MySQL Stack
###  Dependencies
1. **[Apache 2.4.54](https://httpd.apache.org/docs/)** or later
2. **[PHP 8.1.10](https://www.php.net/downloads)** or later
3. **[MySQL Server 8.0.31](https://dev.mysql.com/downloads/mysql/)** or later
4. **[Composer](https://getcomposer.org/download/)** 
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
git clone <repo> /path/to/www/folder
```
Navigate to the cloned folder.
```bash
cd /path/to/www/folder
```

### Install Dependencies
Install the composer dependencies by running:
```bash
composer install
``` 

### Configure and Run the Application
Update the values in `/app/config/config.php` file to match your local environment. 
>**NB:**
The values can be passed as environment variables instead of hard coding them in the config file. 
Usually, the .env file would be used for doing this.
You can optionally leverage the secrets.php file for this. 
Kindly look at the [secrets-example.php](/app/config/secrets-example.php) file for more information on how to do this.

#### Set up the Database
With MySQL server running, create the `iuhrms` database from a terminal window:

```bash
mysql -uroot -p
```
```sql
CREATE DATABASE IF NOT EXISTS iuhrms
```

Create the tables using the `001-create.sql` file located in the `sql-scripts` folder inside the project directory.

```bash
mysql -u root -p iuhrms < "sql-scripts/001-create.sql"
```

To insert records into the database, run:
```bash
mysql -u root -p iuhrms < "sql-scripts/002-insert.sql"
```

Alternatively, you can use a GUI application such as [MySQL Workbench](https://www.mysql.com/products/workbench/) or the web-based database manager [phpMyAdmin](https://www.phpmyadmin.net/) to connect to the Mysql server, create the database and run the sql files in sql-scripts folder to create the tables and populate the database.

### Test the Local Deployment
If you are using a standard Apache + PHP setup, use a web browser to navigate to **[http://localhost]()**.
This assumes that you have cloned the project to the default document root folder of your apache installation.

## Container Deployment Using Docker

###  Dependencies
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

### Configure and Run the Application
Pull the Docker images from the Docker registry
```bash
docker pull ecakubi/iuhrms:latest
docker pull ecakubi/mysql:latest
```

Alternatively, you can build the images from the Dockerfiles in the project directory.
```bash
docker build -t ecakubi/iuhrms:latest -f Dockerfile-apache .
docker build -t ecakubi/mysql:latest -f Dockerfile-mysql . 
```

Create a docker network for the containers to communicate with each other.
```bash
docker network create docker-local
```

You may need to create a storage folder for bind mounting the mysql database folder. This folder will be used to persist the database files even after the container is destroyed.
```bash
mkdir storage
```

Update the .env file with the values appropriate for your environment.

Spin up docker containers from the images:
```bash
docker run -d -p 80:80 -p 443:443 --network docker-local --env-file .env --name iuhrms ecakubi/iuhrms
docker run -d -p 3306:3306 -v %cd%/sql-scripts:/docker-entrypoint-initdb.d --network docker-local --env-file .env --name mysql ecakubi/mysql
```
>**Note:**
> On Linux, `%cd%` may not work in the shell for getting the current working directory.
> In this case, use the `pwd` command to get the current directory as shown below:
```bash
docker run -d -p 80:80 -p 443:443 --network docker-local --env-file .env --name iuhrms ecakubi/iuhrms
```
You can access the application on the host machine by visiting http://localhost in a web browser.

