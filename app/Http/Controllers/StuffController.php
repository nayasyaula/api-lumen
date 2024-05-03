<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\Stuff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StuffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function index()
    {
        try {
            $data = Stuff::with('stock')->get();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }

    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required',
                'category' => 'required',
            ]);
            $stuff = Stuff::create([
                'name' => $request->input('name'),
                'category' => $request->input('category'),
            ]);
            return ApiFormatter::sendResponse(201, true, 'Barang berhasil disimpan!', $stuff);
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
            $stuff = stuff::with('stock')->findOrFail($id);

            return ApiFormatter::sendResponse(200, true, "Lihat barang dengan id $id", $stuff);
        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "Data dengan id $id tidak ditemukan");
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $stuff = stuff::findOrFail($id);
            $name = ($request->name) ? $request->name : $stuff->name;
            $category = ($request->category) ? $request->category : $stuff->category;

            $stuff->update([
                'name' => $name,
                'category' => $category
            ]);

            return ApiFormatter::sendResponse(200, true, "Berhasil ubah dataa dengan id $id");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->GetMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $stuff = Stuff::findOrFail($id);

            if ($stuff->inboundStuffs()->exists()) {
                return ApiFormatter::sendResponse(400, "bad request", "Tidak dapat menghapus data stuff, sudah terdapat data inbound");
            } 
            elseif ($stuff->stocks()->exists()) {
                return ApiFormatter::sendResponse(400, "bad request", "Tidak dapat menghapus data stuff, sudah terdapat data stuff stock");
            }
            elseif($stuff->lendings()->exists()) {
                return ApiFormatter::sendResponse(400, "bad request", "Tidak dapat menghapus data stuff, sudah terdapat data lending");
            } 
            elseif ($stuff->inboundStuffs()->exists() && $stuff->stocks()->exists() && $stuff->lendings()->exists()) {
                return ApiFormatter::sendResponse(400, "bad request", "Tidak dapat menghapus data stuff, sudah terdapat data inbound/stuff stock/lending");
            }
            else {
                $stuff->delete();

                return ApiFormatter::sendResponse(200, true, "berhasil hapus data barang dengan id $id", ['id' => $id]);
            }

        } catch (\Throwable $th) {

            return ApiFormatter::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->getMessage());
        }
    }

    public function deleted()
    {
        try {
            $stuffs = Stuff::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, true, "lihat data barang yang dihapus", $stuffs);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $stuff = stuff::onlyTrashed()->where('id', $id);

            $stuff->restore();

            return ApiFormatter::sendResponse(200, true, "Berhasil mengembalikan data yang telah dihapus!", ['id' => $id]);
        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silakan coba lagi!", $th->getMessage());
        }
    }

    public function restoreAll()
    {
        try {
            $stuff = stuff::onlyTrashed();

            $stuff->restore();

            return ApiFormatter::sendResponse(200, true, "Berhasil mengembalikan data yang telah dihapus!");
        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silakan coba lagi!", $th->getMessage());
        }
    }

    public function permanentDelete($id)
    {
        try {
            $stuff = stuff::onlyTrashed()->where('id', $id)->forceDelete();

            return ApiFormatter::sendResponse(200, true, "Berhasil hapus permanen data yang telah dihapus!", ['id' => $id]);
        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silakan coba lagi!", $th->getMessage());
        }
    }

    public function permanentDeleteAll()
    {
        try {
            $stuff = stuff::onlyTrashed();

            $stuff->forceDelete();

            return ApiFormatter::sendResponse(200, true, "Berhasil hapus permanen data yang telah dihapus!");
        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silakan coba lagi!", $th->getMessage());
        }
    }

}
