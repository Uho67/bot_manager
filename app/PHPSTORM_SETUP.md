# PhpStorm PHPCS Setup - Real-time Code Checking

## Step 1: Configure PHP Interpreter (Docker)

1. Open **Settings/Preferences** (⌘, on Mac / Ctrl+Alt+S on Windows)
2. Go to **PHP**
3. Click **...** (three dots) next to **CLI Interpreter**
4. Click **+** (plus icon) at the top left
5. Select one of these (depending on your PhpStorm version):
   - **From Docker, Vagrant, VM, WSL, Remote...** OR
   - **From Docker, Vagrant, Remote** OR
   - Just **Docker** or **Docker Compose**
6. In the dialog, select **Docker Compose**
7. Docker server: Select **Docker** (if not configured, click **New...** and accept defaults)
8. Configuration file(s): Browse to or paste:
   ```
   /Users/uho0613/projects/symfony/bot_api/docker/docker-compose.dev.yml
   ```
9. Service: Select **php** from dropdown
10. Click **OK**
11. Wait for PhpStorm to connect - you should see **PHP 8.4.x** detected
12. Give it a name like "Docker PHP 8.4" (optional)
13. Click **OK** to close all dialogs

## Step 2: Configure PHP_CodeSniffer

1. Still in **Preferences**
2. Go to **PHP → Quality Tools → PHP_CodeSniffer**
3. Configuration: Select your Docker interpreter from dropdown
4. Click **...** next to Configuration
5. PHP_CodeSniffer path: `/var/www/html/vendor/bin/phpcs` (path inside Docker)
6. Click **Validate** - should show "OK, PHP_CodeSniffer version X.X.X"
7. Click **OK**

## Step 3: Set Coding Standard

1. Still in **PHP_CodeSniffer** settings
2. Under "Coding standard": Select **Custom**
3. Click **...** button
4. Path to ruleset: `/var/www/html/phpcs.xml` (path inside Docker)
5. Click **OK**

## Step 4: Enable Inspection

1. Go to **Preferences → Editor → Inspections**
2. Type "phpcs" in search box
3. Find **PHP → Quality tools → PHP_CodeSniffer validation**
4. Check ✅ the checkbox
5. Set Severity to **Warning** (yellow underline) or **Error** (red underline)
6. Click **OK**

## Step 5: Enable Auto-Fix on Save

1. Go to **Preferences → Tools → Actions on Save**
2. Check ✅ **Optimize imports** (auto-sorts imports alphabetically!)
3. Check ✅ **Reformat code** (optional - applies code style)
4. Click **OK**

## Done! Test It

1. Open `/src/Catalog/Controller/ProductImageController.php`
2. Try adding imports in wrong order:
   ```php
   use Symfony\...;
   use App\...;  // ← Should show yellow underline (App should come first)
   ```
3. Save file (⌘S) - imports auto-sort!
4. Remove a type hint - should show underline immediately

## Shortcuts

- **⌃⌥O** - Optimize imports (sort alphabetically)
- **⌘⌥L** - Reformat code
- **⌥⏎** - Show quick fix on underlined code

## Troubleshooting

**Can't find "From Docker..." option?**
- Update PhpStorm to latest version (2024.3+)
- OR: Look for just "Docker" or "Docker Compose" in the menu
- OR: Install "Docker" plugin from Settings → Plugins

**No underlines showing?**
- Check Inspections are enabled (Step 4)
- Restart PhpStorm
- Make sure file is not in excluded folders

**"Cannot validate phpcs"?**
- Make sure Docker containers are running: `docker ps`
- Check PHP interpreter is set to Docker (Step 1)
- Path should be `/var/www/html/vendor/bin/phpcs` (inside container)

**Imports not auto-sorting?**
- Enable "Optimize imports" in Actions on Save (Step 5)
- Or manually press ⌃⌥O after adding imports

## Alternative: Local Setup (Without Docker)

If Docker configuration is too complex, use local PHP:

### Step 1 Alternative: Use Local PHP
1. **Settings → PHP**
2. CLI Interpreter: Click **...** → **+** → **Local Path**
3. PHP executable: `/usr/local/bin/php` or wherever PHP 8.4+ is installed
4. Click **OK**

### Step 2 Alternative: Local PHPCS Path
1. **Settings → PHP → Quality Tools → PHP_CodeSniffer**
2. Configuration: Click **...**
3. PHP_CodeSniffer path: `/Users/uho0613/projects/symfony/bot_api/app/vendor/bin/phpcs` (local path)
4. Click **Validate**
5. Click **OK**

### Step 3 Alternative: Local Ruleset Path
1. Coding standard: **Custom**
2. Click **...**
3. Path: `/Users/uho0613/projects/symfony/bot_api/app/phpcs.xml` (local path)
4. Click **OK**

Then continue with Step 4 (Enable Inspection) from the main guide.

**Note:** Local setup requires PHP 8.4+ installed on your Mac. If you only have PHP 8.2, stick with Docker setup.

