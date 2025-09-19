# IP Address Management API

---

A RESTful API built with Symfony 6 and PHP 8.4 that manages IP address information.

The API fetches IP data from [ipstack.com](https://ipstack.com) and stores it in a local database, implementing a 24-hour caching system. 
It allows users to retrieve, delete and manage IPs, including bulk operations.

The API also includes a blacklist feature: any blacklisted IP is blocked from retrieval, preventing external API requests.  

All endpoints are documented using OpenAPI, and proper HTTP status codes are used for errors such as `404 Not Found` or `403 Forbidden`.

Unit tests cover core functionality, including caching, blacklisting, deletion, and bulk operations.

---

# Features

- **Retrieve IP information:**
    - Returns cached IP data from the local database if the data is not older than 24 hours.
    - If cached data is older than 24 hours, fetches fresh data from ipstack and updates the database before returning.
    - Fetches and stores new IPs from ipstack if the requested IP is not present in the database.


- **Delete IP information:**
    - Removes an IP from the local database.
    - Returns a success response (`HTTP 204`) on successful deletion.
    - Returns a not-found error (`HTTP 404`) if the IP does not exist.


- **Blacklist management:**
    - Adds IP address to the blacklist.
    - Removes IP addresses from the blacklist.
    - Any request to retrieve information for blacklisted IPs is blocked immediately (`HTTP 403`) without calling the external API.


- **OpenAPI documentation:**
    - All endpoints are documented using OpenAPI/Swagger.
    - Auto-generated API docs are available at `/api/doc`.


- **Extra (bonus):**
    - Bulk endpoints allow batch requests for all main operations, including retrieval, deletion, and blacklist management.

---

# Design Decisions

- Installed PHPUnit and Symfony testing packages to support unit and functional tests.


- Utilized Symfony Client for realistic API testing while keeping tests isolated and efficient.


- Added a global `/api` prefix to all endpoints for consistency and clear API structure.

---

# Getting Started

 Ensure Docker and Docker Compose are installed and running on your machine.

## Ipstack API Key Configuration

This project requires an API key from ipstack to fetch IP address information.

### 1. Register on ipstack:

1. Go to [https://ipstack.com](https://ipstack.com) and click **GET FREE API KEY**.
2. Create a free account.
3. Once logged in, navigate to your **Dashboard**.
4. Copy your **API Access Key**.

### 2. .env.local file addition:

1. In the **project root**, create a new file named `.env.local`.
2. Add the following line to `.env.local`:

```dotenv
IPSTACK_API_KEY=your_api_key_here
```

## Configure Symfony Project

### 1. Clone the repository:
```bash
git clone https://github.com/IgnasSar/junior-php-2025-solution.git
cd junior-php-2025-solution
```

### 2. Start Docker containers:
```bash
docker compose up -d
```

### 3. Install dependencies:
```bash
docker compose exec php composer install
```

### 4. Run database migrations:
```bash
docker exec -it junior-php-2025-solution-php-1 sh
php bin/console doctrine:migrations:migrate
```

---

## About the Tests

 To run tests safely without affecting development or production data, this project uses a dedicated testing environment.

### Environment Variables for Testing

The tests use a separate `.env.test` configuration:

```dotenv
APP_ENV=test
APP_SECRET='$ecretf0rt3st'
DATABASE_URL="sqlite:///%kernel.project_dir%/var/test.db"
IPSTACK_API_KEY="test_key"
```

## Test Description

### 1. **Ip address controller tests:**
    - Retrieve single IP (HTTP 200).
    - Retrieve blacklisted IP (throws `AccessDeniedHttpException`).
    - Retrieve multiple IPs: returns an array of JSON objects for all requested IPs (HTTP 200).
    - Delete single IP (HTTP 204).
    - Delete non-existent IP (throws `NotFoundHttpException`).


### 2. **Ip blacklist controller tests:**
    - Add single IP to blacklist (HTTP 201).
    - Add already blacklisted IP (throws `RuntimeException`).
    - Add multiple IPs: returns an array of added IPs in JSON (HTTP 201).
    - Delete single blacklisted IP (HTTP 204).
    - Delete non-existent blacklisted IP (throws `NotFoundHttpException`).

## Starting the Tests

### 1. Make sure Docker containers are running:

```bash
docker compose up -d
```

### 2. Execute PHPUnit inside the PHP container:

```bash
docker exec -it junior-php-2025-solution-php-1 sh
php bin/phpunit
```
