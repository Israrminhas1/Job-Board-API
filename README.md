<p align="center">
  <img align="center" height="200" src="public/symfony.svg">
</p>
<h1>Job Board API</h1>
The Job Board API is a RESTful API developed with Symfony for managing companies, job postings, applicants, and job applications. Users can sign up and log in to use the various routes, including creating, reading, updating, and deleting entities, as well as searching and filtering job postings. The API offers user authentication, input data validation, and quality tools such as PHPStan and PHPCodeSniffer.

<h2>Requirements</h2>
To install the Job Board API, you will need to have PHP version 8.1 or greater installed on your computer. You can check your current PHP version by running the following command in your terminal:

```shell
php -v
```

If you don't have PHP installed or need to upgrade to the required version, you can download it from the official PHP <a href="https://www.php.net/downloads.php">website</a>.

Next, you will need to install Composer, a dependency manager for PHP, which you can download from the official Composer <a href="https://getcomposer.org/download/">website</a>. Once installed, you can navigate to the project directory in your terminal and run the following command to install the required dependencies:
```shell
composer install
```

XAMPP is required to install and run the Job Board API locally. It provides the necessary environment to run PHP applications, including a web server, a database, and PHP itself. XAMPP is available for Windows, macOS, and Linux and can be downloaded from the Apache Friends <a href="https://www.apachefriends.org/">website</a>.

Optionally, you can install Symfony CLI, a command-line interface for managing Symfony applications. This can be helpful for running Symfony commands and managing the application's environment. You can download and install Symfony CLI from the official Symfony <a href="https://symfony.com/download">website</a>.

<h2>Installation</h2>
1. Clone the repository:

```shell
git clone https://gitlab.com/israrminhas99/job-board-api.git
```

2. Access the directory:

```shell
cd job-board-api/
```

3. Install the Composer dependencies:

```shell
composer install
```

4. Create the database `job-board-api` 
5. Create a file `.env.local` and add configuration:

```dotenv
DATABASE_URL="mysql://root:@localhost:3306/job-board-api"
```

6. Migrate Database tables:

```
php bin/console doctrine:migrations:migrate
```

7. Run the application:

```shell
symfony server:start
```

8. Navigate to http://localhost:8000