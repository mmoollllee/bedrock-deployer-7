<?php

/**
 * Deployer recipes to push Bedrock database from local development
 * machine to a server and vice versa.
 *
 * Assumes that Bedrock runs locally on a Virtual machine and uses
 * "vagrant ssh -- -t" (or whatever is defined as {{vm_shell}} ) command to run WP CLI on local server.
 *
 * Will always create a DB backup on the target machine.
 *
 * Requires these Deployer variables to be set:
 *   local_root: Absolute path to website root on local host machine
 *   trellis_dir: Absolute path to directory that contains trellis folder
 *   vm_root: Absolute path to website inside Virtual machine (should mirror local_root)
 */

namespace Deployer;

require(__DIR__ . '/../lib/functions.php');

set('bin/wp', function () {
    run("if ! [ -f {{deploy_path}}/.dep/wp-cli.phar ]; then
    curl -o {{deploy_path}}/.dep/wp-cli.phar -sS -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
  fi");
    return '{{bin/php}} {{deploy_path}}/.dep/wp-cli.phar';
});

desc('Pulls DB from server and installs it locally, after having made a backup of local DB');
task('pull:db', function () use ($getLocalEnv, $getRemoteEnv, $urlToDomain) {

    // Export db
    $exportFilename = '_db_export_' . date('Y-m-d_H-i-s') . '.sql';
    $exportAbsFile  = get('deploy_path') . '/' . $exportFilename;
    writeln("<comment>Exporting server DB to {$exportAbsFile}</comment>");
    run("cd {{current_path}} && {{bin/wp}} db export {$exportAbsFile}");

    // Download db export
    $downloadedExport = get('local_root') . '/' . $exportFilename;
    writeln("<comment>Downloading DB export to {$downloadedExport}</comment>");
    download($exportAbsFile, $downloadedExport);

    // Cleanup exports on server
    writeln("<comment>Cleaning up {$exportAbsFile} on server</comment>");
    run("rm {$exportAbsFile}");

    // Create backup of local DB
    $backupFilename = '_db_backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backupAbsFile  = get('local_root') . '/' . $backupFilename;
    writeln("<comment>Making backup of DB on local machine to {$backupAbsFile}</comment>");
    runLocally("cd {{trellis_dir}} && {{vm_shell}} wp db export {$backupFilename}");

    // Empty local DB
    writeln("<comment>Reset local DB</comment>");
    runLocally("cd {{trellis_dir}} && {{vm_shell}} wp db reset");

    // Import export file
    writeln("<comment>Importing {$downloadedExport}</comment>");
    // Remove sandbox mode from export file, if it exists
    // This is necessary, because the export file may contain sandbox mode settings
    // which will cause the import to fail.
    runLocally("sed -i '' '/sandbox mode/d' {$downloadedExport}");
    runLocally("cd {{trellis_dir}} && {{vm_shell}} wp db import {$exportFilename}");

    // Load remote .env file and get remote WP URL
    if (!$remoteUrl = $getRemoteEnv()) {
        return;
    }

    // Load local .env file and get local WP URL
    if (!$localUrl = $getLocalEnv()) {
        return;
    }

    // Also get domain without protocol and trailing slash
    $localDomain = $urlToDomain($localUrl);
    $remoteDomain = $urlToDomain($remoteUrl);

    // Update URL in DB
    // In a multisite environment, the DOMAIN_CURRENT_SITE in the .env file uses the new remote domain.
    // In the DB however, this new remote domain doesn't exist yet before search-replace. So we have
    // to specify the old (remote) domain as --url parameter.
    writeln("<comment>Updating the URLs in the DB</comment>");
    runLocally("cd {{trellis_dir}} && {{vm_shell}} wp search-replace '{$remoteUrl}' '{$localUrl}' --skip-themes --url='{$remoteDomain}' --network");
    // Also replace domain (multisite WP also uses domains without protocol in DB)
    runLocally("cd {{trellis_dir}} && {{vm_shell}} wp search-replace '{$remoteDomain}' '{$localDomain}' --skip-themes --url='{$remoteDomain}' --network");
    // Flush Permalinks
    runLocally( "cd {{trellis_dir}} && {{vm_shell}} wp rewrite flush --hard" );

    // Perform additional search and replace operations
    $local_remote = get('local_remote');
    if (isset($local_remote) && is_array($local_remote)) {
        writeln(print_r($local_remote));
        foreach ($local_remote as $local => $remote) {
            writeln("<comment>Replacing '{$remote}' with '{$local}' in the DB</comment>");
            runLocally("cd {{trellis_dir}} && {{vm_shell}} wp search-replace '{$remote}' '{$local}' --skip-themes --network");
        }
    }

    // Cleanup exports on local machine
    writeln("<comment>Cleaning up {$downloadedExport} on local machine</comment>");
    runLocally("rm {$downloadedExport}");
});

desc('Pushes DB from local machine to server and installs it, after having made a backup of server DB');
task('push:db', function () use ($getLocalEnv, $getRemoteEnv, $urlToDomain) {

    // Export db on Virtual Machine
    $exportFilename = '_db_export_' . date('Y-m-d_H-i-s') . '.sql';
    $exportAbsFile  = get('local_root') . '/' . $exportFilename;
    writeln("<comment>Exporting Virtual Machine DB to {$exportAbsFile}</comment>");
    runLocally("cd {{trellis_dir}} && {{vm_shell}} wp db export {$exportFilename}");

    // Upload export to server
    $uploadedExport = get('current_path') . '/' . $exportFilename;
    writeln("<comment>Uploading export to {$uploadedExport} on server</comment>");
    upload($exportAbsFile, $uploadedExport);

    // Cleanup local export
    writeln("<comment>Cleaning up {$exportAbsFile} on local machine</comment>");
    runLocally("rm {$exportAbsFile}");

    // Create backup of server DB
    $backupFilename = '_db_backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backupAbsFile  = get('deploy_path') . '/' . $backupFilename;
    writeln("<comment>Making backup of DB on server to {$backupAbsFile}</comment>");
    run("cd {{current_path}} && {{bin/wp}} db export {$backupAbsFile}");

    // Empty server DB
    writeln("<comment>Reset server DB</comment>");
    run("cd {{current_path}} && {{bin/wp}} db reset");

    // Import export file
    writeln("<comment>Importing {$uploadedExport}</comment>");
    run("cd {{current_path}} && {{bin/wp}} db import {$uploadedExport}");

    // Load remote .env file and get remote WP URL
    if (!$remoteUrl = $getRemoteEnv()) {
        return;
    }

    // Load local .env file and get local WP URL
    if (!$localUrl = $getLocalEnv()) {
        return;
    }

    // Also get domain without protocol and trailing slash
    $localDomain = $urlToDomain($localUrl);
    $remoteDomain = $urlToDomain($remoteUrl);

    // Update URL in DB
    // In a multisite environment, the DOMAIN_CURRENT_SITE in the .env file uses the new remote domain.
    // In the DB however, this new remote domain doesn't exist yet before search-replace. So we have
    // to specify the old (local) domain as --url parameter.
    writeln("<comment>Updating the URLs in the DB</comment>");
    run("cd {{current_path}} && {{bin/wp}} search-replace \"{$localUrl}\" \"{$remoteUrl}\" --skip-themes --url='{$localDomain}' --network");
    // Also replace domain (multisite WP also uses domains without protocol in DB)
    run("cd {{current_path}} && {{bin/wp}} search-replace \"{$localDomain}\" \"{$remoteDomain}\" --skip-themes --url='{$localDomain}' --network");

    // Perform additional search and replace operations
    $local_remote = get('local_remote');
    if (isset($local_remote) && is_array($local_remote)) {
        writeln(print_r($local_remote));
        foreach ($local_remote as $local => $remote) {
            writeln("<comment>Replacing '{$local}' with '{$remote}' in the DB</comment>");
            run("cd {{trellis_dir}} && {{vm_shell}} wp search-replace '{$local}' '{$remote}' --skip-themes --network");
        }
    }

    // Flush Permalinks
    run( "cd {{current_path}} && {{bin/wp}} rewrite flush --hard" );
    
    // Cleanup uploaded file
    writeln("<comment>Cleaning up {$uploadedExport} from server</comment>");
    run("rm {$uploadedExport}");
});
