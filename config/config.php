<?php

/** 
 * Holds default configuration. Copy and paste set-methods to your deploy.php to overwrite them.
 */

namespace Deployer;

require(__DIR__ . '/../../../autoload.php');

use Dotenv\Dotenv;

// .env will be generated on first run.
if (file_exists(get('local_root').'/.env')) {
	$localEnv = Dotenv::createUnsafeImmutable(get('local_root'), '.env');
	$localEnv->load();
}

require __DIR__ . '/../../../deployer/deployer/recipe/common.php';
require __DIR__ . '/../recipe/recipes.php';

// CLI Configuration
// set('bin/composer', function () { return 'composer'; });
set('bin/composer', function () { return '/opt/plesk/php/8.1/bin/php /usr/lib/plesk-9.0/composer.phar'; });
set('composer_options', 'install --verbose --no-interaction');

// Common Deployer config
set('keep_releases', function () { return getenv('DEP_KEEP_RELEASES'); });
set( 'repository', function () { return getenv('DEP_REPO'); });
set('branch', function () { return getenv('DEP_BRANCH') ?: 'master'; });
set( 'shared_dirs', ['web/app/uploads'] );
set( 'domain', basename(get('local_root')) );

// Bedrock DB config
set( 'trellis_dir', get('local_root') . '/../trellis' );
set( 'vm_root', '/srv/www/' . get('domain') . '/current' );
set( 'vm_shell', 'trellis vm shell --workdir ' . get('vm_root') . ' --' );

// Bedrock DB and Sage config
set( 'theme_path', function () { return getenv('DEP_THEME_PATH'); });

// File transfer config
set( 'sync_dirs', [
	get('local_root') . '/web/app/uploads/' => '{{deploy_path}}/shared/web/app/uploads/',
] );
