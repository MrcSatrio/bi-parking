<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TransaksiModel;
use App\Models\KartuModel;
use App\Models\UserModel;

class XenditCallback extends BaseController
{
    protected $transaksiModel;
    protected $userModel;
    protected $kartuModel;
    protected $xenditCallbackToken; // Store the Xendit callback token here or in the environment variable

    public function __construct()
    {
        $this->transaksiModel = new TransaksiModel();
        $this->kartuModel = new KartuModel();
        $this->userModel = new UserModel();
        $this->xenditCallbackToken = 'cYJxEJ38axyZym1Wjl3ua9aTiYfryiORBZGCJSTXNDsFhgz6'; // Xendit callback token
    }

    public function xenditCallback()
    {
        // Token received from Xendit
        $xIncomingCallbackTokenHeader = isset($_SERVER['HTTP_X_CALLBACK_TOKEN']) ? $_SERVER['HTTP_X_CALLBACK_TOKEN'] : "";

        if ($xIncomingCallbackTokenHeader === $this->xenditCallbackToken) {
            // Incoming Request is verified coming from Xendit
            $rawRequestInput = file_get_contents("php://input");
            $arrRequestInput = json_decode($rawRequestInput, true);

            if ($arrRequestInput === null) {
                // Error handling for invalid JSON data
                session()->setFlashdata('error', 'Invalid JSON data in the request.');
                http_response_code(400);
                return;
            }

            $_externalId = $arrRequestInput['external_id'];
        
            $transaksi = $this->transaksiModel
                ->join('user', 'user.npm = transaksi.npm')
                ->join('kartu', 'kartu.id_kartu = user.id_kartu')
                ->join('jenis_pembayaran', 'jenis_pembayaran.id_jenis_pembayaran = transaksi.id_jenis_pembayaran')
                ->where('kodebooking_transaksi', $_externalId)
                ->first();
            
            $pengguna = $this->userModel
                ->join('transaksi', 'transaksi.npm = user.npm')
                ->join('kartu', 'kartu.id_kartu = user.id_kartu')
                ->where('kodebooking_transaksi', $_externalId)
                ->first();
            
            if ($transaksi) {
                $this->transaksiModel->save([
                    'id_transaksi' => $transaksi['id_transaksi'],
                    'saldoawal_transaksi' => $transaksi['saldo'],
                    'saldoakhir_transaksi' => $transaksi['saldo'] + $transaksi['nominal_transaksi'],
                    'nominal_transaksi' => $transaksi['nominal_transaksi'],
                    'id_status_transaksi' => 3,
                    'validator' => 'XENDIT'
                ]);
            
                $kartuData = [
                    'id_kartu' => $transaksi['id_kartu'],
                    'saldo' => $transaksi['saldo'] + $transaksi['nominal_transaksi'],
                ];
                $this->kartuModel->save($kartuData);
                session()->setFlashdata('success', 'Transaksi berhasil dilakukan melalui Xendit.');
            } else {
                // Optionally, you can add an error message here.
                session()->setFlashdata('error', 'Transaksi tidak ditemukan.');
            }
            
            // ... Continue with the previous code for handling Xendit callback token ...
        }}}            