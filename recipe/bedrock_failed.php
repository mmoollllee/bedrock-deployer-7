<?php

/**
 * Cleans up after a failed deploy.
 *
 * Deployer only hooks deploy:failed to its own "deploy" task, so a failed
 * "update" kept the lock and left its release behind. Deployer counts such
 * an aborted release as previous_release and keeps it under keep_releases,
 * so the next cleanup could remove the last working release instead.
 *
 * Both steps only touch what this run created: the lock it acquired and the
 * release it started, never the live one.
 */

namespace Deployer;

fail( 'update', 'deploy:failed' );

after( 'deploy:lock', function () {
    set( 'bedrock_lock_acquired', true );
} );

after( 'deploy:release', function () {
    set( 'bedrock_started_release', get( 'release_name' ) );
} );

desc( 'Removes the release and the lock of a failed deploy' );
task( 'bedrock:failed', function () {
    if ( has( 'bedrock_started_release' ) ) {
        $release = (string) get( 'bedrock_started_release' );
        $current = test( '[ -h {{current_path}} ]' ) ? basename( run( 'readlink {{current_path}}' ) ) : '';

        if ( ctype_digit( $release ) && $release !== $current ) {
            run( "rm -rf {{deploy_path}}/releases/{$release}" );
            run( '[ ! -h {{deploy_path}}/release ] || rm {{deploy_path}}/release' );
            warning( "Removed aborted release {$release}." );
        }
    }

    if ( has( 'bedrock_lock_acquired' ) ) {
        invoke( 'deploy:unlock' );
    }
} )->hidden();

after( 'deploy:failed', 'bedrock:failed' );
