<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\InboundStuff;
use App\Models\Stuff;
use App\Models\StuffStock;
use Illuminate\Http\Request;

class InboundController extends Controller
{

    public function index()
    {
        $inbound = InboundStuff::with('stuff')->get();
        $stuff = Stuff::get();
        $stock = StuffStock::get();

        $data = ['barang' => $stuff, 'stock' => $stock];

        return ApiFormatter::sendResponse(200, true, 'Lihat semua inbound', $inbound);
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'stuff_id' => 'required',
                'proff_file' => 'required|file|max:2048', // Max size 2MB
            ]);
            $file = $request->file('proff_file');

            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            $file->move('files', $fileName);
            $InboundStuff = InboundStuff::create([
                'stuff_id' => $request->input('stuff_id'),
                'total' => $request->input('total'),
                'date' => $request->input('date'),
                'proff_file' => $fileName,
            ]);

            $Stock = StuffStock::where('stuff_id', $request->input('stuff_id'))->first();

            $total_Stock = (int)$Stock->total_avaible + (int)$request->input('total');

            $Stock->update([
                'total_avaible' => (int)$total_Stock
            ]);

            if ($InboundStuff && $Stock) {
                return ApiFormatter::sendResponse(201, true, 'Inbound barang berhasil disimpan');
            } else {
                return ApiFormatter::sendResponse(400, false, 'Inbound barang gagal disimpan');
            }

            return ApiFormatter::sendResponse(201, true, 'Inbound barang berhasil disimpan', [$InboundStuff , 'file_path' => 'files/' . $fileName]);

        } catch (\Throwable $th) {
            //throw $th;
            // if ($th->validator->errors()) {
            //     return ApiFormatter::sendResponse(400, false, 'Terdapat kesalahan input silakan coba lagi!', $th->validator->errors());
            // } else {
            //     return ApiFormatter::sendResponse(400, false, 'Terdapat kesalahan input silakan coba lagi!', $th->getMessage());
            // }
        }

    }
    public function show($id)
    {
        try {
            $inbound = InboundStuff::with('stuff', 'stuff.stock')->findOrFail($id);

            return ApiFormatter::sendResponse(200, true, 'Lihat inbound barang dengan id $id', $inbound);
        } catch (\Throwable $th) {
            //throw $th;

            return ApiFormatter::sendResponse(404, false, "Data dengan id $id tidak ditemukan");
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $inbound = InboundStuff::with('stuff')->findOrFail($id);
            $total = ($request->total) ? $request->total : $inbound->total;
            $date = ($request->date) ? $request->date : $inbound->date;
            $proff_file = ($request->proff_file) ? $request->proff_file : $inbound->proff_file;

            $inbound->update([
                'total' => $total,
                'date' => $date,
                'proff_file' => $proff_file,
            ]);

            $file = $request->file('proff_file');

            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            $file->move('files', $fileName);

            return ApiFormatter::sendResponse(200, true, 'Berhasil ubah data inbound dengan id $id', [$inbound , 'file_path' => 'files/' . $fileName]);
        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->GetMessage());
        }
    }

    public function destroy($id)
    {
        // pada fitur hapus inbound stuff, tambahlah logic pengkondisian agar data inbound stuff 
        // tidak dapat dihapus apabila total_available pada stuff_stocks lebih kecil dari total pada inbounds
        try {
            $inbound = InboundStuff::findOrFail($id);

            $data = StuffStock::where('stuff_id', $inbound->stuff_id)->first();

            if ($data->total_available < $inbound->total) {
                $inbound->delete();

                return ApiFormatter::sendResponse(404, false, 'Proses gagal total_available pada stuff_stocks lebih kecil dari total pada inbounds');
            } else {
                return ApiFormatter::sendResponse(200, true, 'Berhasil hapus data dengan id $id', [ 'id' => $id,]);
            }

        } catch (\Throwable $th) {

            return ApiFormatter::sendResponse(404, false, "Proses gagal silahkan coba lagi", $th->getMessage()); 
        }
    }

    public function deleted()
    {
        try {
            $inbounds = InboundStuff::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, true, 'Lihat data inbound barang yang di hapus', $inbounds);
        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, 'Proses gagal! Silakan coba lagi!', $th->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $inbound = InboundStuff::onlyTrashed()->findOrFail($id);
            $has_inbound = InboundStuff::where('stuff_id', $inbound->stuff_id)->get();

            if ($has_inbound->count() == 1) {
                $message = "Data stok sudah ada, tidak boleh ada duplikat data stok untuk satu barang silakan update data stok dengan id stok $inbound->stuff_id";
            } else {
                $inbound->restore();
                $message = "Berhasil mengembalikan data yang telah di hapus!";
            }

            return ApiFormatter::sendResponse(200, true, $message, ['id' => $id, 'stuff_id' => $inbound->stuff_id]);

        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "Proses gagal! Silakan coba lagi", $th->getMessage());
        }
    }

    public function restoreAll()
    {
        try {
            $inbounds = InboundStuff::onlyTrashed()->restore();

            return ApiFormatter::sendResponse(200, true, "Berhasil mengembalikan semua data yang telang dihapus!");

        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "Proses gagal! Silakan coba lagi", $th->getMessage());
        }
    }

    public function permanentDelete($id)
    {
        try {
            $InboundStuff = InboundStuff::onlyTrashed()->where('id', $id)->first();

            unlink(base_path('public/proof/'.$InboundStuff->proof_file));

            $checkProses = InboundStuff::where('id', $id)->forceDelete();

            return ApiFormatter::sendResponse(200, true, "Berhasil hapus permanen data yang telah di hapus", ['id' => $id]);

        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false,"Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function permanentDeleteAll()
    {
        try {
            $inbounds = InboundStuff::onlyTrashed()->forceDelete();

            return ApiFormatter::sendResponse(200, true, "Berhasil hapus permanen data yang telah di hapus!");
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silahkan coba lagi!", $th->getMessage());
        }
    }

    public function upload(Request $request)
    {
        try {
            // Validate the request
            $this->validate($request, [
                'proff_file' => 'required|file|max:2048', // Max size 2MB
            ]);
            // Get the file from the request
            $file = $request->file('proff_file');

            // Generate a unique name for the file
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Move the file to the storage directory
            $file->move('files', $fileName);

            // Return a response with the file path
            return response()->json(['file_path' => 'files/' . $fileName]);
        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(400, false, 'Terdapat kesalahan input silakan coba lagi!', $th->getMessage());
        }
    }

    private function deleteAssociatedFile(InboundStuff $InboundStuff)
    {
        $publicPath = $_SERVER['DOCUMENT_ROOT'] . '/public/proof';

        $filePath = \public_path('proof/'.$InboundStuff->proof_file);
 
        if (file_exists($filePath)) {

            unlink(base_path($filePath));
        }
    }

    public function __construct()
    {
        $this->middleware('auth:api');
    }
}
