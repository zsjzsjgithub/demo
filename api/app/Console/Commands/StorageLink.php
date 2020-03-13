<?php
/**
 * Created by PhpStorm.
 * User: tbphp
 * Date: 2019/2/24
 * Time: 9:22 PM
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StorageLink extends Command
{
    protected $signature = 'storage:link';

    protected $description = '创建资源存储软连接';

    public function handle()
    {
        $link = base_path('public/storage');
        is_dir($link) && unlink($link);
        symlink(config('filesystems.storage_path'), $link);
    }

}