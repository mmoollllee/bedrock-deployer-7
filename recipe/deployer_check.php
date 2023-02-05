<?php

/**
 * Check for configuration files before starting
 */

namespace Deployer;

/*
 * Check for .env.deployer and if not present, create it
 */

desc( '.env.deployer present?' );
task( 'deployer:check', function () {

    // Check if local .env.deployer exists
    if ( testLocally('[ -f {{local_root}}/.env.deployer ]')) {
        return;
    }

    // If not create one

    writeln( '<comment>Generating .env.deployer file</comment>' );

    // Ask for credentials
    $repo = ask( 'Bedrock Repository' );
    $prod_dir = ask( 'Production Directory', '~/httpdocs' );
    $stage_dir = ask( 'Stage Directory', '~/stage.'.get('domain') );
    $prod_user = ask( 'Production Username' );
    $stage_user = ask( 'Stage Username', $prod_user );
    $theme_name = ask( 'themes name for template_path?', 'template' );
    $keep_releases = ask( 'Releases to keep', '4' );

    ob_start();

    echo <<<EOL
REPO='{$repo}'
DIR='{$prod_dir}'
STAGE_DIR='{$stage_dir}'
USERNAME='{$prod_user}'
STAGE_USERNAME='{$stage_user}'
THEME_PATH='web/app/themes/{$theme_name}'
KEEP_RELEASES={$keep_releases}

EOL;

    $content = ob_get_clean();



    runLocally( 'echo "' . $content . '" > {{local_root}}/.env.deployer' );

    writeln( '<comment>Rerun task please</comment>' );
    return;

} );
