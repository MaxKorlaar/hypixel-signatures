# Testing Setup Guide

## Overview

This guide explains how to set up and run the comprehensive test suite for the Hypixel Signatures application, both locally and in GitHub Actions.

## Local Testing Setup

### Prerequisites

1. PHP 8.1 or higher
2. Composer
3. A valid Hypixel API key (get one from [Hypixel Developer Portal](https://developer.hypixel.net/))

### Setup Steps

1. **Install Dependencies**
   ```bash
   composer install
   ```

2. **Set up Environment Configuration**
   
   Copy the testing environment file:
   ```bash
   cp .env.testing .env.test
   ```
   
   Or create your own `.env.test` file with the following configuration:
   ```env
   APP_ENV=testing
   APP_KEY=base64:YourTestingKeyHere
   HYPIXEL_API_KEY=your-actual-hypixel-api-key-here
   DB_CONNECTION=sqlite
   DB_DATABASE=:memory:
   CACHE_DRIVER=array
   SESSION_DRIVER=array
   QUEUE_CONNECTION=sync
   ```

3. **Generate Application Key** (if needed)
   ```bash
   php artisan key:generate --env=testing
   ```

4. **Set Your Hypixel API Key**
   
   Replace `your-actual-hypixel-api-key-here` in your `.env.test` file with your actual Hypixel API key.

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run only feature tests
./vendor/bin/phpunit tests/Feature/

# Run only unit tests
./vendor/bin/phpunit tests/Unit/

# Run specific test class
./vendor/bin/phpunit tests/Feature/PlayerStatusTest.php

# Run with verbose output
./vendor/bin/phpunit --verbose

# Run with coverage (requires Xdebug)
./vendor/bin/phpunit --coverage-html coverage/
```

## GitHub Actions / CI Setup

To run tests in GitHub Actions, you need to configure the required secrets in your GitHub repository.

### Setting up GitHub Secrets

1. **Navigate to Repository Settings**
   - Go to your GitHub repository
   - Click on "Settings" tab
   - Select "Secrets and variables" → "Actions" from the left sidebar

2. **Add Required Secrets**
   
   Click "New repository secret" and add the following:

   | Secret Name | Description | Value |
   |-------------|-------------|-------|
   | `HYPIXEL_API_KEY` | Your Hypixel API key | Get from [Hypixel Developer Portal](https://developer.hypixel.net/) |

3. **Optional Secrets**
   
   You may also want to add these for enhanced testing:

   | Secret Name | Description | Example Value |
   |-------------|-------------|---------------|
   | `APP_KEY` | Laravel application key | `base64:RandomKeyGeneratedByLaravel` |

### GitHub Actions Workflow

Your `.github/workflows/tests.yml` should include:

```yaml
name: Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: mbstring, xml, ctype, iconv, intl, pdo_sqlite
        coverage: xdebug
    
    - name: Install dependencies
      run: composer install --prefer-dist --no-progress
    
    - name: Copy environment file
      run: cp .env.testing .env
    
    - name: Generate application key
      run: php artisan key:generate
    
    - name: Set Hypixel API key
      run: echo "HYPIXEL_API_KEY=${{ secrets.HYPIXEL_API_KEY }}" >> .env
    
    - name: Run tests
      run: ./vendor/bin/phpunit --coverage-clover=coverage.xml
    
    - name: Upload coverage to Codecov
      uses: codecov/codecov-action@v3
      with:
        file: ./coverage.xml
```

## Test Configuration Details

### Database

The tests use SQLite in-memory database (`sqlite::memory:`) for fast, isolated testing. No database setup is required.

### Cache and Sessions

Tests use array drivers for cache and sessions to avoid external dependencies like Redis.

### API Dependencies

Tests are designed to handle external API failures gracefully:
- **Hypixel API**: Tests expect various response codes (200, 404, 500) and handle timeouts
- **Mojang API**: Player skin/head tests accommodate API unavailability

### Test Data

The tests use the UUID `b876ec32-e396-476b-a115-8438d83c67d4` as the primary test player. This UUID should be:
- A valid Minecraft player UUID format
- Accessible via Hypixel API
- Used consistently across all relevant tests

## Troubleshooting

### Common Issues

1. **Missing Hypixel API Key**
   ```
   Error: No API key configured
   ```
   **Solution**: Set `HYPIXEL_API_KEY` in your environment file or GitHub secrets.

2. **API Rate Limiting**
   ```
   Error: 429 Too Many Requests
   ```
   **Solution**: Tests are designed to handle this gracefully. Ensure you're not running tests too frequently.

3. **External API Failures**
   ```
   Error: Failed to connect to api.hypixel.net
   ```
   **Solution**: Tests should pass even with API failures. Check that tests are properly handling error cases.

4. **Database Issues**
   ```
   Error: Database connection failed
   ```
   **Solution**: Ensure you're using SQLite in-memory database in testing environment.

### Debug Mode

To enable debug output in tests:

```bash
# Run with debug output
./vendor/bin/phpunit --debug

# Run specific test with full error details
./vendor/bin/phpunit tests/Feature/PlayerStatusTest.php --verbose --debug
```

### API Testing

You can test API connectivity manually:

```bash
# Test Hypixel API connectivity
curl -H "Authorization: Bearer YOUR_API_KEY" "https://api.hypixel.net/status"

# Test with test UUID
curl -H "Authorization: Bearer YOUR_API_KEY" "https://api.hypixel.net/player?uuid=b876ec32-e396-476b-a115-8438d83c67d4"
```

## Test Coverage

The test suite covers:

- ✅ **Player Status** - Online status checking (UUID and username)
- ✅ **Player Images** - Skin and head image generation
- ✅ **Guild Information** - Guild pages and statistics
- ✅ **Signature Images** - All signature generation endpoints
- ✅ **Application Integration** - Routing, middleware, CORS
- ✅ **Utilities** - Core validation and configuration functions

## Contributing

When adding new tests:

1. Use the standard test UUID `b876ec32-e396-476b-a115-8438d83c67d4`
2. Handle external API failures gracefully
3. Test both success and error cases
4. Follow existing naming conventions
5. Update this README if adding new test requirements