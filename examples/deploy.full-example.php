<?php

namespace Deployer;

require 'vendor/deployer/deployer/recipe/common.php';
require 'vendor/mmoollllee/bedrock-deployer/recipe/bedrock_db.php';
require 'vendor/mmoollllee/bedrock-deployer/recipe/bedrock_env.php';
require 'vendor/mmoollllee/bedrock-deployer/recipe/bedrock_misc.php';
require 'vendor/mmoollllee/bedrock-deployer/recipe/common.php';
require 'vendor/mmoollllee/bedrock-deployer/recipe/filetransfer.php';
require 'vendor/mmoollllee/bedrock-deployer/recipe/sage.php';
require 'vendor/mmoollllee/bedrock-deployer/recipe/trellis.php';

set('bin/composer', function () { return 'composer'; });

// Configuration

// Common Deployer config
set( 'repository', 'git@github.com:example/example.git' );
set( 'shared_dirs', [
	'web/app/uploads'
] );

// Bedrock DB config
set( 'vagrant_dir', dirname( __FILE__ ) . '/../trellis' );
set( 'vagrant_root', '/srv/www/example.com/current' );

// Bedrock DB config
set( 'local_root', dirname( __FILE__ ) );

// Sage config
set( 'theme_path', 'web/app/themes/template' );

// File transfer config
set( 'sync_dirs', [
	dirname( __FILE__ ) . '/web/app/uploads/' => '{{deploy_path}}/shared/web/app/uploads/',
] );


// Hosts

set( 'default_stage', 'staging' );

host( 'stage.example.com' )
	->stage( 'staging' )
	->user( 'user' )
	->set( 'deploy_path', '~/stage.example.com/deploy' );

host( 'stillhammerhaus.de' )
	->stage( 'production' )
	->user( 'user' )
	->set( 'deploy_path', '~/httpdocs' );


// Tasks

// Deployment flow
desc( 'Deploy your project' );
task( 'deploy', [
	'deploy:prepare',
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
	'push:db',
	'deploy:unlock',
	'cleanup',
	'success',
] );

// [Optional] if deploy fails automatically unlock.
after( 'deploy:failed', 'deploy:unlock' );
