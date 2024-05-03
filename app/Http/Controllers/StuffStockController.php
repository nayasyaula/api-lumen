<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\Stuff;
use App\Models\StuffStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StuffStockController extends Controller
{
    public function index()
    {
        $stuffStock = StuffStock::with('stuff')->get();
        $stuff = Stuff::get();
        $stock = StuffStock::get();

        $data = ['barang' => $stuff, 'stock' => $stock];

        return ApiFormatter::sendResponse(200, true, 'Lihat semua stock barang', $stuffStock);
    }

    public function store(Request $request)
    {
       try {
            $this->validate($request, [
                'stuff_id' => 'required',
            ]);
            $stock = StuffStock::updateOrCreate([
                'stuff_id' => $request->input('stuff_id'),
            ], [
                'total_available' => $request->input('total_available'), 
                'total_defect' => $request->input('total_defect'),
            ]);
            return ApiFormatter::sendResponse(201, true, 'Stock barang berhasil disimpan', $stock);
        } catch (\Throwable $th) {
            //throw $th;
            if ($th->validator->errors()) {
                return ApiFormatter::sendResponse(400, false, 'Terdapat kesalahan input silakan coba lagi!', $th->validator->errors());
            } else {
                return ApiFormatter::sendResponse(400, false, 'Terdapat kesalahan input silakan coba lagi!', $th->getMessage());
            }
        }
    }

    public function show($id)
    {

        try {
            $stock = StuffStock::with('stuff')->findOrFail($id);

            return ApiFormatter::sendResponse(200, true, 'Lihat stock barang dengan id $id', $stock);
        } catch (\Throwable $th) {
            //throw $th;

            return ApiFormatter::sendResponse(404, false, "Data dengan id $id tidak ditemukan");
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $stock = StuffStock::with('stuff')->findOrFail($id);
            $total_available = ($request->total_available) ? $request->total_available : $stock->total_available;
            $total_defect = ($request->total_defect) ? $request->total_defect : $stock->total_defect;

            $stock->update([
                'total_available' => $total_available,
                'total_defect' => $total_defect,
            ]);

            return ApiFormatter::sendResponse(200, true, 'Berhasil ubah data stock dengan id $id', $stock);
        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->GetMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $stock = StuffStock::findOrFail($id);

            $stock->delete();

            return ApiFormatter::sendResponse(200, true, 'Berhasil hapus data dengan id $id', [ 'id' => $id,]);
        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->getMessage()); 
        }
    }

    public function deleted()
    {
        try {
            $stocks = StuffStock::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, true, 'Lihat data stock barang yang di hapus', $stocks);
        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, 'Proses gagal! Silakan coba lagi!', $th->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $stock = StuffStock::onlyTrashed()->findOrFail($id);
            $has_stock = StuffStock::where('stuff_id', $stock->stuff_id)->get();

            if ($has_stock->count() == 1) {
                $message = "Data stok sudah ada, tidak boleh ada duplikat data stok untuk satu barang silakan update data stok dengan id stok $stock->stuff_id";
            } else {
                $stock->restore();
                $message = "Berhasil mengembalikan data yang telah di hapus!";
            }

            return ApiFormatter::sendResponse(200, true, $message, ['id' => $id, 'stuff_id' => $stock->stuff_id]);

        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "Proses gagal! Silakan coba lagi", $th->getMessage());
        }
    }

    public function restoreAll($id)
    {
        try {
            $stocks = StuffStock::onlyTrashed()->restore();

            return ApiFormatter::sendResponse(200, true, "Berhasil mengembalikan semua data yang telang dihapus!");

        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "Proses gagal! Silakan coba lagi", $th->getMessage());
        }
    }

    public function permanentDelete($id)
    {
        try {
            $stock = StuffStock::onlyTrashed()->where('id', $id)->forceDelete();

            return ApiFormatter::sendResponse(200, true,
            "Berhasil hapus permanen data yang telah di hapus", ['id' => $id]);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false,
            "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function permanentDeleteAll()
    {
        try {
            $stocks = StuffStock::onlyTrashed()->forceDelete();

            return ApiFormatter::sendResponse(200, true,
            "Berhasil hapus permanen data yang telah di hapus!");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false,
            "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function __construct()
    {
        $this->middleware('auth:api');
    }
}