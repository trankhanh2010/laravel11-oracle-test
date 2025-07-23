<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BlockMigrateRefresh extends Command
{
    protected $signature = 'migrate:refresh';

    protected $description = 'Sửa View => Tạo file migration mới => drop view cũ => ghi view mới. Không sửa file migration cũ.';

    public function handle()
    {
        $this->error('Sửa View => Tạo file migration mới => drop view cũ => ghi view mới. Không sửa file migration cũ.');
        return 1; // Trả về mã lỗi
    }
}

