<?php

/**
 * Miscellaneous Bedrock tasks.
 */

namespace Deployer;

/*
 * Runs Composer install for Bedrock
 */
desc( 'Installing Bedrock vendors' );
task( 'bedrock:vendors', function () {
    run( 'cd {{release_path}} && {{bin/composer}} {{composer_options}}' );
} );

desc( 'Create WP-SCSS Cache Folder' );
task( 'bedrock:wp-scss', function () {
    run( 'mkdir {{release_path}}/web/app/plugins/wp-scss/cache');
} );
