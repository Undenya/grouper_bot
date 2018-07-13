<?php
/**
 * Created by PhpStorm.
 * User: UnDenya
 * Date: 11.07.2018
 * Time: 19:34
 */
include (__DIR__.'/vendor/autoload.php');
spl_autoload_register(function ($class) {

    if (file_exists('application/controller/' . $class . '.php'))
    {
        include 'application/controller/' . $class . '.php';
    }
    elseif(file_exists('application/model/' . $class . '.php'))
    {
        include 'application/model/' . $class . '.php';
    }
});

if (!file_exists('madeline.php')) {
    copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
}
include 'madeline.php';

$settings = [
    'logger' => ['logger_level' => \danog\MadelineProto\Logger::ERROR],
    'peer' => ['full_fetch' => false, 'cache_all_peers_on_startup' => false],
    'updates' => ['handle_old_updates' => false],
];
$MadelineProto = new \danog\MadelineProto\API('session.madeline', $settings);
//$MadelineProto->session = __DIR__.'/session.madeline2';
//$MadelineProto->settings['logger']['logger_level'] =  \danog\MadelineProto\Logger::ERROR;
//$MadelineProto->settings['peer']['full_fetch'] = false;
//$MadelineProto->settings['peer']['cache_all_peers_on_startup'] = false;
//$MadelineProto->settings['updates']['handle_old_updates'] = false;
//$MadelineProto->start();


$bot = new C_Bot;
$bot->startUp($MadelineProto);