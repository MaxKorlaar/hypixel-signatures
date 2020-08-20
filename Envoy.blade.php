@include('vendor/autoload.php')

@setup
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
@endsetup

@servers(['web' => [$_ENV['DEPLOY_SSH']] ])

@story('full-deploy')
down
git
composer
optimize
node
build
up
@endstory

@story('simple-deploy')
git
optimize
build
@endstory

@finished
@discord($_ENV['LOG_DISCORD_WEBHOOK_URL'])
@endfinished

@task('optimize')
cd /var/www/hypixel-signatures
php artisan optimize
@endtask

@task('down')
cd /var/www/hypixel-signatures
php artisan down
@endtask

@task('up')
cd /var/www/hypixel-signatures
php artisan up
@endtask

@task('git')
cd /var/www/hypixel-signatures
git pull
@endtask

@task('composer')
cd /var/www/hypixel-signatures
composer install
@endtask

@task('node')
cd /var/www/hypixel-signatures
pnpm install --shamefully-hoist
@endtask

@task('build')
cd /var/www/hypixel-signatures
pnpm run prod
@endtask
