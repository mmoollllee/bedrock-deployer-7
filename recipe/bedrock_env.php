<?php

/**
 * Manages the Bedrock .env file on the server.
 *
 * The .env file of the live release is copied into the new release. If the
 * live release has none, the newest release that has one is used: an aborted
 * deploy leaves a release without .env behind, and Deployer still counts it
 * as previous_release.
 *
 * Only if no release has a .env file, the task asks for credentials and
 * options, generates salts and writes a new one. It refuses to do so without
 * an interactive terminal, so a deploy with --no-interaction can never create
 * a .env file from default answers (empty DB password).
 */

namespace Deployer;

use Deployer\Exception\Exception;

/**
 * Generates a random token with a length of 64 chars.
 *
 * Bases on wp_generate_password() function.
 *
 * @return string
 */
function bedrock_generate_salt() {
    $chars              = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~+=,.;:/?|';
    $char_option_length = strlen( $chars ) - 1;

    $password = '';
    for ( $i = 0; $i < 64; $i ++ ) {
        $password .= substr( $chars, random_int( 0, $char_option_length ), 1 );
    }

    return $password;
}

/**
 * Quotes a value for the .env file.
 *
 * phpdotenv takes single-quoted values literally but cannot escape a single
 * quote inside them, so such values are double-quoted with backslash escapes.
 *
 * @param string $value
 *
 * @return string
 */
function bedrock_env_value( $value ) {
    if ( strpos( $value, "'" ) === false ) {
        return "'" . $value . "'";
    }

    return '"' . addcslashes( $value, '"\\' ) . '"';
}

/*
 * Copies the .env file of the live release, or of the newest release that
 * has one. If there is none, the .env file is created while prompting the
 * user for credentials.
 */
desc( 'Makes sure, .env file for Bedrock is available' );
task( 'bedrock:env', function () {

    $sources = [ '{{current_path}}/.env' ];
    if ( has( 'previous_release' ) ) {
        $sources[] = '{{previous_release}}/.env';
    }
    foreach ( get( 'releases_list' ) as $release ) {
        $sources[] = "{{deploy_path}}/releases/{$release}/.env";
    }

    foreach ( $sources as $source ) {
        if ( test( "[ -f {$source} ]" ) ) {
            run( "cp {$source} {{release_path}}/.env" );
            return;
        }
    }

    if ( ! input()->isInteractive() ) {
        throw new Exception( 'No .env file found in the current or any other release. Run the deploy without --no-interaction to create one.' );
    }

    // Keys that require a salt token
    $salt_keys = [
        'AUTH_KEY',
        'SECURE_AUTH_KEY',
        'LOGGED_IN_KEY',
        'NONCE_KEY',
        'AUTH_SALT',
        'SECURE_AUTH_SALT',
        'LOGGED_IN_SALT',
        'NONCE_SALT',
    ];

    writeln( '<comment>Generating .env file</comment>' );

    // Ask for credentials
    $wp_domain = ask( get( 'stage' ) . ' server WordPress domain (ie domain.com)', get('domain')  );
    $db_name = ask( get( 'stage' ) . ' server WordPress DB name', $wp_domain );
    $db_user = ask( get( 'stage' ) . ' server WordPress DB user', $wp_domain );
    $db_pass = askHiddenResponse( get( 'stage' ) . ' server WordPress DB password' );
    $db_host = ask( get( 'stage' ) . ' server WordPress DB host', '127.0.0.1' );
    $wp_env  = askChoice( get( 'stage' ) . ' server ENV', ['development' => 'development', 'staging' => 'staging', 'production' => 'production'], 'staging' );
    $wp_prot = askChoice( get( 'stage' ) . ' server protocol', ['http' => 'http', 'https' => 'https'], 'http' );

    $absolute_path = run( 'cd {{deploy_path}} && pwd' );

    $values = [
        'DB_NAME'             => $db_name,
        'DB_USER'             => $db_user,
        'DB_PASSWORD'         => (string) $db_pass,
        'DB_HOST'             => $db_host,
        'WP_ENV'              => $wp_env,
        'WP_HOME'             => "{$wp_prot}://{$wp_domain}",
        'WP_SITEURL'          => "{$wp_prot}://{$wp_domain}/wp",
        'DOMAIN_CURRENT_SITE' => $wp_domain,
        'PROTOCOL'            => $wp_prot,
        'WPCACHEHOME'         => "{$absolute_path}/current/web/app/plugins/wp-super-cache/",
        'CACHE_PATH'          => "{$absolute_path}/current/web/app/cache/",
    ];
    foreach ( $salt_keys as $key ) {
        $values[ $key ] = bedrock_generate_salt();
    }

    $content = '';
    foreach ( $values as $key => $value ) {
        $content .= $key . '=' . bedrock_env_value( $value ) . PHP_EOL;
    }

    // Base64 keeps salts and passwords away from shell expansion and from
    // Deployer's {{placeholders}}; %secret% keeps them out of the output.
    run( 'echo %secret% | base64 -d > {{release_path}}/.env', secret: base64_encode( $content ) );
} );
