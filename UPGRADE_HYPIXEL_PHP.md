# Hypixel-PHP Library Upgrade Guide

## Summary

This document describes the changes made to upgrade from the MaxKorlaar fork of `plancke/hypixel-php` (dev-master) to the official upstream version 1.5.1.

## Problem Statement

The application was experiencing 404 errors from the Hypixel API when attempting to fetch SkyBlock profiles. This was caused by using an outdated fork of the `plancke/hypixel-php` library that was still using the deprecated v1 API endpoints.

## Changes Made

### 1. Updated Composer Dependencies

**File**: `composer.json`

- Changed from: `"plancke/hypixel-php": "dev-master"` (MaxKorlaar fork)
- Changed to: `"plancke/hypixel-php": "^1.5"` (official upstream)
- Removed the custom VCS repository pointing to the MaxKorlaar fork

**Impact**: The application now uses the latest stable version of the library with v2 API endpoints.

### 2. API Endpoint Migration (v1 → v2)

The most critical change is that the library now uses Hypixel API v2 endpoints:

- **Old**: `https://api.hypixel.net/`
- **New**: `https://api.hypixel.net/v2/`

This change is automatic and requires no code changes. It fixes the 404 errors when fetching SkyBlock profiles.

**Affected API calls**:
- SkyBlock profiles: `/skyblock/profile` → `/v2/skyblock/profile`
- Player data: `/player` → `/v2/player`
- Guild data: `/guild` → `/v2/guild`
- All other endpoints

### 3. GameTypes → ServerTypes Migration

**Breaking Change**: The `GameTypes` class was refactored into `ServerTypes` in the upstream library.

**Files Updated** (18 files):
- `app/Http/Controllers/SkyBlockController.php`
- `app/Http/Controllers/Guild/BedWarsController.php`
- `app/Http/Controllers/Guild/GuildController.php`
- `app/Http/Controllers/Guild/MegaWallsController.php`
- `app/Http/Controllers/Guild/MurderMysteryController.php`
- `app/Http/Controllers/Guild/SkyWarsController.php`
- `app/Http/Controllers/Guild/TNTGamesController.php`
- `app/Http/Controllers/Signatures/AnimatedSkyWarsSignatureController.php`
- `app/Http/Controllers/Signatures/BedWarsSignatureController.php`
- `app/Http/Controllers/Signatures/BlitzSurvivalGamesSignatureController.php`
- `app/Http/Controllers/Signatures/CopsAndCrimsSignatureController.php`
- `app/Http/Controllers/Signatures/DuelsSignatureController.php`
- `app/Http/Controllers/Signatures/GeneralSignatureController.php`
- `app/Http/Controllers/Signatures/Guild/GuildSignatureController.php`
- `app/Http/Controllers/Signatures/SimpleSkyWarsSignatureController.php`
- `app/Http/Controllers/Signatures/SkyWarsSignatureController.php`
- `app/Http/Controllers/Signatures/TNTGamesSignatureController.php`
- `app/Http/Controllers/Signatures/UHCChampionsSignatureController.php`

**Changes made**:
```php
// Old import
use Plancke\HypixelPHP\classes\gameType\GameTypes;

// New import
use Plancke\HypixelPHP\classes\serverType\ServerTypes;

// Old usage
$stats = $player->getStats()->getGameFromID(GameTypes::SKYBLOCK);

// New usage
$stats = $player->getStats()->getGameFromID(ServerTypes::SKYBLOCK);
```

**API Compatibility**: All constants and methods remain the same, only the class name and namespace changed:
- Constants: `SKYBLOCK`, `BEDWARS`, `SKYWARS`, etc. (unchanged)
- Methods: `fromEnum()`, `fromID()`, `fromName()`, etc. (unchanged)

### 4. Friends API Removal

The upstream library removed the Friends API endpoints as they are no longer supported by Hypixel. 

**Impact**: None - this application does not use the Hypixel Friends API directly. The "Friends" feature in the application uses a different implementation.

## Other Changes in Upstream (Included)

The upgrade also includes these improvements from the upstream:

1. **PHP 8 Support**: Better compatibility with PHP 8.x
2. **Bedwars Prestiges**: Updated bedwars prestige calculations
3. **User Agent**: Basic user agent header set for API requests
4. **Punishment Stats**: Fixed punishment stats endpoint
5. **Input Validation**: Improved validation and cleanup
6. **Game Data Updates**: Updated data for SkyWars, Survival Games, and Mega Walls

## Testing Recommendations

1. **SkyBlock Profile Loading**: Test fetching SkyBlock profiles to ensure 404 errors are resolved
2. **Signature Generation**: Verify all signature types still generate correctly
3. **Guild Statistics**: Test guild statistics pages for all game types
4. **Cache Behavior**: Monitor cache to ensure profile data is being cached properly

## Rollback Plan

If issues arise, the application can be rolled back by:

1. Reverting `composer.json` to use the fork:
   ```json
   "plancke/hypixel-php": "dev-master"
   ```

2. Re-adding the repository configuration:
   ```json
   "repositories": [
     {
       "type": "vcs",
       "url": "https://github.com/MaxKorlaar/hypixel-php"
     }
   ]
   ```

3. Reverting all `ServerTypes` references back to `GameTypes`

4. Running `composer update plancke/hypixel-php`

## Future Considerations

- **Fork Maintenance**: If the MaxKorlaar fork is still needed for custom changes, it should be updated to merge the latest upstream changes.
- **Version Pinning**: Consider pinning to a specific version tag (e.g., `1.5.1`) instead of using `^1.5` for production stability.
- **API Rate Limiting**: Monitor API usage to ensure the v2 endpoints don't have different rate limits.

## References

- Upstream Repository: https://github.com/Plancke/hypixel-php
- Tag 1.5.0: Use v2 endpoints
- Tag 1.5.1: Support PHP 8
- Hypixel API Documentation: https://api.hypixel.net/
