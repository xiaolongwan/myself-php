<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Config\Src;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for pay',
                    'source' => __DIR__ . '/../../publish/pay.php',
                    'destination' => BASE_PATH . '/config/autoload/pay.php',
                ],
                [
                    'id' => 'config',
                    'description' => 'The config for pushMessage',
                    'source' => __DIR__ . '/../../publish/pushMessage.php',
                    'destination' => BASE_PATH . '/config/autoload/pushMessage.php',
                ],
                [
                    'id' => 'config',
                    'description' => 'The config for live',
                    'source' => __DIR__ . '/../../publish/live.php',
                    'destination' => BASE_PATH . '/config/autoload/live.php',
                ],
            ],
        ];
    }
}
