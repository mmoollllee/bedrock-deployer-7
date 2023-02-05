<?php

/**
 * ACF PRO will be installed via composer (offical support: https://www.advancedcustomfields.com/resources/installing-acf-pro-with-composer/)
 * and needs a `auth.json` in the project directory for authentification
 */

namespace Deployer;

/*
 * Tries to copy auth.json file from previous release to current release.
 * If not available, checks the presence locally and uploads auth.json.
 */

desc( 'Makes sure, auth.json file for ACF PRO is available' );
task( 'bedrock:acf', function () {

    // Try to copy auth.json file from previous release to current release
    if ( has( 'previous_release' ) ) {
        if ( test( "[ -f {{previous_release}}/auth.json ]" ) ) {
            run( "cp {{previous_release}}/auth.json {{release_path}}" );
            return;
        }
    }

    // Check if local auth.json exists
    if ( testLocally('[ -f {{local_root}}/auth.json ]') == false) {
        writeln( '<comment>Local auth.json does not exist. Composer Install might fail if ACF PRO get\'s installed!</comment>' );
        return;
    }

    writeln( '<comment>Uploading auth.json ACF PRO Credentials</comment>' );
    upload( get('local_root') . '/auth.json', '{{release_path}}', [] );
} );
