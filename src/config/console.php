<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------

$commands = [];

if (PHP_SAPI == 'cli') {

    $commands = [
        'cli' => \rolink\console\command\Cli::class
    ];

    $apps = scan_dir(APP_PATH . '*', GLOB_ONLYDIR);

    foreach ($apps as $app) {
        $commandFile = APP_PATH . $app . '/command.php';

        if (file_exists($commandFile)) {
            $mCommands = include $commandFile;
            if (is_array($mCommands)) {
                $commands = array_merge($commands, $mCommands);
            }
        }
    }
}

return [
    // 指令定义
    'commands' => $commands,
];
