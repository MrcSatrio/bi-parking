<?php

namespace App\Models;

use CodeIgniter\Model;

class LogModel extends Model{

    protected $table      = 'log_aktivitas';
    protected $primaryKey = 'id_log';
    protected $allowedFields = [
        'id_log',
        'timestamp',
        'npm',
        'ip_address',
        'action',
        'details',
    ];
}