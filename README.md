# Sample Website Backend

This repository contains the backend for an SPA (Single-Page-Application), built using the Laravel framework. It provides a robust and scalable foundation for web applications, simplifying common tasks like authentication, routing, and database management. The project is configured to run in a Docker environment, making it easy to set up and manage dependencies.

> [!NOTE] 
This app is made to be used with SPA and was developed as a self-learning free-time project, so it might contain inconsistent code or some vulnerabilities. If you want to contribute, feel free to post an PR :)

## Key Technologies

* **Laravel**: A powerful PHP framework for web artisans.
* **Docker**: For containerization and environment management.
* **MySQL**: The default database for the application.

## Prerequisites

Before you begin, ensure you have the following installed on your system:

* [Docker Engine](https://docs.docker.com/engine/install/)
* [Composer](https://getcomposer.org/download/)
* PHP. Required minimum PHP version is always configured in [composer.json](https://github.com/JezSonic/sample-website-backend/blob/7d6e99a83365079beddb34039c626ac094e3228c/composer.json#L9C4-L9C23)

## Getting Started

Follow these steps to set up and run the project locally.

### 1. Clone the Repository
```bash
git clone [https://github.com/JezSonic/sample-website-backend.git](https://github.com/JezSonic/sample-website-backend.git)
cd sample-website-backend
```

### 2. Configure environment variables
```bash
cp .env.example .env
```

### 3. Install composer packages
```bash
compsoer install
```

### 4. Build and run docker image
> [!WARNING]  
Running this command WITHOUT having composer apckages installed will install them from the container which will most likely cause file ownership issues
```bash
docker-compose up -d 
```

### 5. Generate application key and migrate databases
```bash
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
```
