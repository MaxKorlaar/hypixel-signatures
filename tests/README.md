# Hypixel Signatures Test Suite

This test suite provides comprehensive coverage for the Hypixel Signatures application.

## Test Structure

### Feature Tests
- **PlayerStatusTest** - Tests player online status functionality
- **GuildTest** - Tests guild information and statistics endpoints
- **SignatureImageTest** - Tests all signature image generation endpoints
- **PlayerImageTest** - Tests player skin/head image endpoints
- **ApplicationIntegrationTest** - Tests application-wide functionality

### Unit Tests
- **UtilitiesTest** - Tests configuration, validation, and utility functions

## Running Tests

```bash
# Install dependencies first
composer install

# Generate application key
php artisan key:generate

# Run all tests
./vendor/bin/phpunit

# Run specific test suites
./vendor/bin/phpunit tests/Feature/
./vendor/bin/phpunit tests/Unit/

# Run with coverage (if xdebug is enabled)
./vendor/bin/phpunit --coverage-html coverage/
```

## Test Coverage

The test suite covers:

1. **Player Status Endpoints**
   - Player status index page
   - Status by UUID and username
   - JSON API endpoints
   - Error handling for invalid inputs

2. **Guild Functionality**
   - Guild index and info pages
   - Member listings
   - Game statistics (SkyWars, BedWars, TNT Games, MegaWalls, Murder Mystery)
   - General statistics
   - Guild banner generation

3. **Signature Images**
   - All game-specific signatures
   - General and small general signatures
   - SkyBlock signatures (stats, pets, minions)
   - Guild signatures
   - Animated signatures
   - Timestamp signatures

4. **Player Images**
   - Player head images (WebP and PNG)
   - Full skin images (WebP and PNG)
   - Caching behavior

5. **Integration Tests**
   - Friends functionality
   - Privacy page
   - Sitemap generation
   - Route configuration
   - Middleware functionality
   - Error handling

## Test Configuration

The tests are configured to:
- Handle both successful responses and error conditions gracefully
- Test with realistic but safe test data (using well-known Minecraft accounts)
- Account for potential API failures and external dependencies
- Validate image content types and response headers
- Test caching behavior and performance considerations

## Notes

- Tests use Laravel's testing framework and PHPUnit
- External API calls may fail in testing environments - tests account for this
- Image generation tests validate content types but don't validate actual image content
- Tests are designed to be robust and not fail due to external dependencies