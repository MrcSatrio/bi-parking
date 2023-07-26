<?php

namespace App\Controllers\Auth;

use \App\Controllers\BaseController;

class Saldo extends BaseController
{
    protected $userModel;
    public function ceksaldo()
    {
        $cek = $this->request->getVar('npm');
        $saldo = $this->userModel
            ->join('kartu', 'kartu.id_kartu = user.id_kartu')
            ->where('npm', $cek)
            ->first();

        if (!$saldo) {
            return redirect()->to('/')->withInput()->with('msg', 'NIM Tidak Terdaftar');
        }
        $data = [
            'title' => 'Parking Management System',
            'ceksaldo' => $saldo
        ];
        return view('auth/liatsaldo', $data);
    }
}
