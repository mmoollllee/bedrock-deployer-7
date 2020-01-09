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

set('bin/composer', function () { return 'composer'; });

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
	->set( 'deploy_path', '~/stage/deploy' );
// Set Webspace-Path to ~/stage/deploy/current/web/

host( 'stillhammerhaus.de' )
	->stage( 'production' )
	->user( 'user' )
	->set( 'deploy_path', '~/httpdocs/deploy' );


// Tasks

// Deployment flow
desc( 'Deploy your project' );
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
	'bedrock:wp-scss',
	'deploy:clear_paths',
	'push:db',
	'push:files',
	'deploy:unlock',
	'cleanup',
	'success',
] );

// [Optional] if deploy fails automatically unlock.
after( 'deploy:failed', 'deploy:unlock' );
