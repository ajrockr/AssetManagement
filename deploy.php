<?php
namespace Deployer;

require 'recipe/symfony.php';

// Config

set('repository', 'https://github.com/ajrockr/ajrit.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

host('10.10.4.9')
    ->set('remote_user', 'tony')
    ->set('deploy_path', '~/assetmanagement.westex.org');

// Hooks

after('deploy:failed', 'deploy:unlock');
