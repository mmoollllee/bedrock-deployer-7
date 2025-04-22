<?php

/**
 * - Deploy with `php vendor/bin/dep deploy stage`
 * - Set Webspace Path to 'current/web'
 */

namespace Deployer;

// Configurate Hostname of stage & production
$hostname = getenv('DEP_HOSTNAME');
$stage_hostname = getenv('DEP_STAGE_HOSTNAME') ?: $hostname;

// get directory of projects. Will be used for domain name,...
set( 'local_root', dirname( __FILE__ ) );

require 'vendor/mmoollllee/bedrock-deployer-7/config/config.php';

// set 
host( 'stage' )
	->setHostname( $stage_hostname )
	->set('remote_user', function () { return getenv('DEP_STAGE_USERNAME') ?: getenv('DEP_USERNAME'); })
	->set('deploy_path', function () { return getenv('DEP_STAGE_DIR'); });

host( 'prod' )
	->setHostname( $hostname )
	->set('remote_user', function () { return getenv('DEP_USERNAME'); })
	->set('deploy_path', function () { return getenv('DEP_DIR'); });

// Tasks
desc( 'Deploy whole project' );
task( 'deploy', [
	'deployer:check',
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
	'deployer:check',
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
