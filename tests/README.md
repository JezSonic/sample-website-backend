# Testing Documentation

This document provides an overview of the testing approach for the website-backend project.

## Test Structure

The tests are organized into two main directories:

- `Feature`: Contains tests that test the application as a whole, including HTTP requests, database interactions, etc.
- `Unit`: Contains tests that test individual components of the application in isolation.

### Feature Tests

Feature tests are further organized into subdirectories:

- `Database`: Tests for database seeders and factories
- `Http/Controllers`: Tests for HTTP controllers
- `Models`: Tests for models with their relationships

### Unit Tests

Unit tests are organized into subdirectories:

- `Models`: Tests for individual models

## Models and Factories

The following models have factories and tests:

- `User`: The central model representing a user of the application
- `GitHubUserData`: Contains GitHub-specific user data
- `GoogleUserData`: Contains Google-specific user data
- `UserLoginActivity`: Records user login activities
- `UserProfileSettings`: Contains user profile settings

## Running Tests

To run all tests:

```bash
php artisan test
```

To run a specific test:

```bash
php artisan test --filter=UserTest
```

To run tests with coverage report:

```bash
php artisan test --coverage
```

## Database Seeding

The database seeder creates:

1. A test user with all related models
2. 5 additional random users with:
   - 50% chance to have GitHub data
   - 50% chance to have Google data
   - All users have profile settings
   - Random number (0-5) of login activities

To seed the database:

```bash
php artisan db:seed
```

## Test Data

The factories create realistic test data for all models. You can customize the data by passing an array of attributes to the factory:

```php
$user = User::factory()->create([
    'name' => 'Custom Name',
    'email' => 'custom@example.com',
]);
```

You can also create related models:

```php
$user = User::factory()
    ->has(GitHubUserData::factory())
    ->has(GoogleUserData::factory())
    ->has(UserProfileSettings::factory())
    ->has(UserLoginActivity::factory()->count(3))
    ->create();
```
