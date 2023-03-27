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

3. Install the composer dependencies:

```shell
composer install
```

4. Create the database `job-board-api` 
5. Create a file `.env.local` and add configuration:

```dotenv
DATABASE_URL="mysql://root:@localhost:3306/job-board-api"
```

6. Migrate database tables:

```
php bin/console doctrine:migrations:migrate
```

7. Run the application:

```shell
symfony server:start
```

8. Navigate to http://localhost:8000

<h2>API Documentation</h2>
You can find the API documentation at [http://localhost:8000/api/doc]. This documentation provides details about the available endpoints, the required input parameters, and the expected response format.
<h2>Validations</h2>
The API includes input data validations to ensure that the data provided by the client is in the correct format and meets the necessary requirements. These validations are enforced using Symfony's built-in validation components.

<h2>Authentication</h2>
User authentication is implemented using JSON Web Tokens (JWT). Clients must sign up and log in to the application to obtain a token that can be used to access the secured route

<h2>Quality Tools</h2>

The project includes quality tools such as PHPStan (level 5) and PHPCodeSniffer to ensure that the code is clean and follows best practices. These tools are integrated with the project's CI/CD pipeline to provide automated testing and code analysis.

Install PHP CS Fixer:

```shell
composer install --working-dir=tools/php-cs-fixer
```

Run PHP CS Fixer:

```shell
php tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src --rules=@PSR12
```

Install PHPStan:

```shell
composer require --dev phpstan/phpstan-symfony
```

Run PHPStan:

```shell
 ./vendor/bin/phpstan analyze src/ --level 5
```

<h2>Avaialble Routes</h2>

* `POST /api/v1/auth/register`: Creates a new user account.

* `POST /api/v1/login_check`: Logs in a user and returns a JWT token.

* `GET /api/v1/companies`: Returns a list of all companies.

* `POST /api/v1/companies`: Creates a new company.

* `GET /api/v1/companies/{id}`: Returns a specific company.

* `PUT /api/v1/companies/{id}`: Updates a specific company.

* `DELETE /api/v1/companies/{id}`: Deletes a specific company.

* `GET /api/v1/jobs`: Returns a list of all jobs.

* `POST /api/v1/jobs`: Creates a new job.

* `GET /ap/v1i/jobs/{id}`: Returns a specific job.

* `GET /api/v1/jobs/{id}/applicants`: Returns a list of applicants for a specific job.

* `PUT /api/v1/jobs/{id}`: Updates a specific job.

* `DELETE /api/v1/jobs/{id}`: Deletes a specific job.

* `GET /api/v1/applicants`: Returns a list of all applicants.

* `POST /api/v1/applicants`: Creates a new applicant.

* `GET /api/v1/applicants/{id}`: Returns a specific applicant.

* `GET /api/v1/applicants/{id}/jobs`: Returns a list of jobs applied for by a specific applicant.

* `PUT /api/v1/applicants/{id}`: Updates a specific applicant.

* `DELETE /api/v1/applicants/{id}`: Deletes a specific applicant.

* `GET /api/v1/job_applicants`: Returns a list of all applications.

* `POST /api/v1/job_applicants`: Submits a new job application.

* `DELETE /api/v1/job_applicants/{jobId}/{applicantId}`: Deletes an existing job application.

