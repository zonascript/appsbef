<?php

namespace App\Http\Controllers;

use App\DataPeserta;
use App\ProgramStudi;
use App\Universitas;
use Illuminate\Http\Request;

use App\Http\Requests;
use Symfony\Component\VarDumper\Cloner\Data;

class Export extends Controller
{
    public function index()
    {
//        $data = DataPeserta::where(['status_pembayaran_id' => 3, 'nomor_tiket' => null])->get();
//
//        foreach($data as $d){
//            $ceknomor = DataPeserta::where('jenis_tiket_id', $d->jenis_tiket_id)->whereNotNull('nomor_tiket')->count();
//            echo 'nomor = '.$ceknomor .' - jenis - '.$d->jenis_tiket_id. ' - p id - '.$d->peserta_id.'<br>';
//            if($ceknomor <= 0){
//                $d->update([
//                    'nomor_tiket' => (1),
//                ]);
//                echo 'new number - 1 <br>';
//            } else{
//                $d->update([
//                    'nomor_tiket' => ($ceknomor + 1),
//                ]);
//                echo 'new number -'.($ceknomor + 1).'- to jt '.$d->jenis_tiket_id. ' p id - '.$d->peserta_id;
//            }
////
////            $d->update([
////                'nomor_tiket' => $nomortiket,
////            ]);
//        }

        $data = DataPeserta::where('status_pembayaran_id', 3)->get();
        foreach($data as $d){
            $d->update([
                'nomor_tiket' => null,
            ]);
        }

        foreach($data as $ds){

            $ceknomor = \App\DataPeserta::where('jenis_tiket_id', $ds->jenis_tiket_id)->orderBy('nomor_tiket', 'desc')->first();

            if($ceknomor->nomor_tiket == null) {
                $nomortiket = 1;
            } else {
                $nomortiket = $ceknomor->nomor_tiket + 1;
            }

            $ds->update([
                'nomor_tiket' => $nomortiket,
            ]);
        }

        return "ok";

    }

    private function generateKodePembayaran($jenis)
    {
        $k = '';
        if($jenis == 1)
            $k = 'A';
        else if($jenis == 2)
            $k = 'B';
        else if($jenis == 3)
            $k = 'C';
        else if($jenis == 4)
            $k = 'D';
        else if($jenis == 5)
            $k = 'E';
        else if($jenis == 6)
            $k = 'F';
        else if($jenis == 7)
            $k = 'G';

        $gen = $k.date('d').rand(0, 100);

        $cek = DataPeserta::where('kode_pembayaran', $gen)->count();

        if($cek >= 1 ) return $this->generateKodePembayaran($jenis);
        else return $gen;
    }

    public function process(Request $request)
    {
        ini_set('max_execution_time', 3600);

        $nama_folder = public_path() . '/csv/';
        $file = $request->file('file');
        $name = $file->getClientOriginalName();
        $ext = $file->guessClientExtension();
        $size = $file->getClientSize();


        $fulldir = $nama_folder . $name;
        $file->move($nama_folder, $name);

        $csv = new parseCSV($fulldir);

        foreach ($csv->data as $item) {

            $univ = Universitas::where('nama', $item['UNIVERSITAS'])->first();

            if($univ == null){
                $univ = Universitas::create([
                    'nama' => $item['UNIVERSITAS'],
                    'kode' => '-'
                ]);
            }

            $prodi = ProgramStudi::create([
                'kode' => $item['KODE'],
                'nama' => $item['PROGRAMSTUDI'],
                'universitas_id' => $univ->id,
                'kategori_id' => $item['JURUSAN']
            ]);

        }

        unlink($fulldir);

        return "ok";
    }
}
