<?php

/**
 * Deploy with `php vendor/bin/dep deploy stage`
 */

namespace Deployer;

require 'vendor/deployer/deployer/recipe/common.php';
require 'vendor/mmoollllee/bedrock-deployer/recipe/recipes.php';

// Configuration
set('bin/composer', function () { return '/opt/plesk/php/8.1/bin/php /usr/lib/plesk-9.0/composer.phar'; }); // set('bin/composer', function () { return 'composer'; });
set('composer_options', 'install --verbose --no-interaction');
set('bin/wp', function () {
	run("cd {{deploy_path}} && curl -sS -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar");
	run('mv {{deploy_path}}/wp-cli.phar {{deploy_path}}/.dep/wp-cli.phar');
	return '{{bin/php}} {{deploy_path}}/.dep/wp-cli.phar';
});

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

host( 'stage' )
	->setHostname( 'example.com' )
	->set('remote_user', 'user')
	->set( 'deploy_path', '~/httpdocs/deploy' );
// Set Webspace-Path to ~/stage/deploy/current/web/

host( 'prod' )
	->setHostname( 'example.com' )
	->set('remote_user', 'user')
	->set( 'deploy_path', '~/httpdocs/deploy' );


// Tasks
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
	'bedrock:acf',
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
	'bedrock:acf',
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
