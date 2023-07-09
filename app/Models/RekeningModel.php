<?php

namespace App\Models;

use CodeIgniter\Model;

class RekeningModel extends Model{

    protected $table      = 'rekening';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id',
        'bank',
        'no_rekening',
        'nama_rekening',
    ];
}