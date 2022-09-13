<?php

namespace Deployer;

require 'vendor/deployer/deployer/recipe/common.php';
require 'vendor/mmoollllee/bedrock-deployer/recipe/prepare.php';
require 'vendor/mmoollllee/bedrock-deployer/recipe/bedrock_db.php';
require 'vendor/mmoollllee/bedrock-deployer/recipe/bedrock_env.php';
require 'vendor/mmoollllee/bedrock-deployer/recipe/bedrock_misc.php';
require 'vendor/mmoollllee/bedrock-deployer/recipe/common.php';
require 'vendor/mmoollllee/bedrock-deployer/recipe/filetransfer.php';
require 'vendor/mmoollllee/bedrock-deployer/recipe/sage.php';
require 'vendor/mmoollllee/bedrock-deployer/recipe/trellis.php';

// Configuration
// set('bin/composer', function () { return 'composer'; });
set('bin/composer', function () { return '/opt/plesk/php/8.1/bin/php /usr/lib/plesk-9.0/composer.phar'; });

// Common Deployer config
set( 'repository', 'git@github.com:example/example.git' );
set( 'shared_dirs', ['web/app/uploads'] );

// Bedrock DB config
set( 'vagrant_dir', dirname( __FILE__ ) . '/../trellis' );
set( 'vagrant_root', '/srv/www/example.com/current' );

// Bedrock DB and Sage config
set( 'local_root', dirname( __FILE__ ) );;
set( 'theme_path', 'web/app/themes/template' );

// File transfer config
set( 'sync_dirs', [
	dirname( __FILE__ ) . '/web/app/uploads/' => '{{deploy_path}}/shared/web/app/uploads/',
] );

// Hosts

host( 'stage.example.com' )
	->set('labels', ['stage' => 'stage'])
	->set('remote_user', 'user')
	->set( 'deploy_path', '~/stage/deploy' );
// Set Webspace-Path to ~/stage/deploy/current/web/

host( 'example.com' )
	->set('labels', ['stage' => 'production'])
	->set('remote_user', 'user')
	->set( 'deploy_path', '~/httpdocs/deploy' );


// Tasks

// Deployment flow
// ToDo adapt sage:vendors...
desc( 'Deploy whole project' );
task( 'deploy', [
	'bedrock:prepare',
	'deploy:lock',
	'deploy:release',
	'deploy:update_code',
	'trellis:remove',
	'deploy:shared',
	'deploy:writable',
	'deploy:symlink',
	'bedrock:env',
	'bedrock:vendors',
	'deploy:clear_paths',
	'push:db',
	'push:files-no-bak',
	'deploy:unlock',
	'deploy:cleanup',
	'deploy:success',
] );

desc( 'Deploy only app' );
task( 'update', [
	'bedrock:prepare',
	'deploy:lock',
	'deploy:release',
	'deploy:update_code',
	'trellis:remove',
	'deploy:shared',
	'deploy:writable',
	'bedrock:env',
	'bedrock:vendors',
	'deploy:clear_paths',
	'deploy:symlink',
	'deploy:unlock',
	'deploy:cleanup',
	'deploy:success',
] );

task( 'pull', [
	'pull:db',
	'pull:files',
] );

// [Optional] if deploy fails automatically unlock.
after( 'deploy:failed', 'deploy:unlock' );
