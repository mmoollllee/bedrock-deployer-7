<?php

/** 
 * Holds default configuration. Copy and paste set-methods to your deploy.php to overwrite them.
 */

namespace Deployer;

require(__DIR__ . '/../../../autoload.php');

use Dotenv\Dotenv;

// .env.deployer will be generated on first run.
if (file_exists(get('local_root').'/.env.deployer')) {
	$localEnv = Dotenv::createUnsafeImmutable(get('local_root'), '.env.deployer');
	$localEnv->load();
}

require __DIR__ . '/../../../deployer/deployer/recipe/common.php';
require __DIR__ . '/../recipe/recipes.php';

// CLI Configuration
// set('bin/composer', function () { return 'composer'; });
set('bin/composer', function () { return '/opt/plesk/php/8.1/bin/php /usr/lib/plesk-9.0/composer.phar'; });
set('composer_options', 'install --verbose --no-interaction');

// Common Deployer config
set('keep_releases', function () { return getenv('KEEP_RELEASES'); });
set( 'repository', function () { return getenv('REPO'); });
set('branch', function () { return getenv('BRANCH') ?: 'master'; });
set( 'shared_dirs', ['web/app/uploads'] );
set( 'domain', basename(get('local_root')) );

// Bedrock DB config
set( 'trellis_dir', get('local_root') . '/../trellis' );
set( 'vm_root', '/srv/www/' . get('domain') . '/current' );
set( 'vm_shell', 'trellis vm shell --workdir ' . get('vm_root') . ' --' );

// Extra options appended to every WP-CLI `db` subcommand that runs on the server.
// Those subcommands shell out to the mysql/mariadb client binaries, and since
// MariaDB Connector/C 3.4 the client verifies the database server's certificate by
// default. Hosts whose database presents a self-signed certificate need
// '--ssl-verify-server-cert=0' here, which keeps the connection encrypted but skips
// the untrusted-chain check.
set( 'db_cli_args', '' );

// Bedrock DB and Sage config
set( 'theme_path', function () { return getenv('THEME_PATH'); });

// File transfer config
set( 'sync_dirs', [
	get('local_root') . '/web/app/uploads/' => '{{deploy_path}}/shared/web/app/uploads/',
] );
