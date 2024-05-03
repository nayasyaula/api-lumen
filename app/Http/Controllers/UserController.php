<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $user = User::get();

        return ApiFormatter::sendResponse(200, true, 'Lihat semua user', $user);
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'username' => 'required',
                'email' => 'required',
                'password' => 'required',
                'role' => 'required',
            ]);
            $user = User::create([
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'password' => $request->input('password'),
                'role' => $request->input('role', ['staff', 'admin']),
            ]);

            return ApiFormatter::sendResponse(201, true, 'User berhasil di tambahkan', $user);
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
            $user = User::findOrFail($id);

            return ApiFormatter::sendResponse(200, true, 'Lihat user dengan id $id', $user);
        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "Data dengan id $id tidak ditemukan");
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $username = ($request->username) ? $request->username : $user->username;
            $email = ($request->email) ? $request->email : $user->email;
            $password = ($request->password) ? $request->password : $user->password;
            $role = ($request->role) ? $request->role : $user->role;

            $user->update([
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'role' => $role
            ]);

            return ApiFormatter::sendResponse(200, true, "Berhasil ubah user dengan id $id");
        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->GetMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            $user->delete();

            return ApiFormatter::sendResponse(200, true, "berhasil hapus data user dengan id $id", ['id' => $id]);

        } catch (\Throwable $th) {

            return ApiFormatter::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->getMessage());
        }
    }

    public function deleted()
    {
        try {
            $users = User::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, true, "lihat data user yang dihapus", $users);
        } catch (\Throwable $th) {
            return ApiFormatter::sendResponse(404, false, "proses gagal silahkan coba lagi", $th->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            $user = User::onlyTrashed()->where('id', $id);

            $user->restore();

            return ApiFormatter::sendResponse(200, true, "Berhasil mengembalikan data yang telah dihapus!", ['id' => $id]);
        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silakan coba lagi!", $th->getMessage());
        }
    }

    public function restoreAll()
    {
        try {
            $user = User::onlyTrashed();

            $user->restore();

            return ApiFormatter::sendResponse(200, true, "Berhasil mengembalikan data yang telah dihapus!");
        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silakan coba lagi!", $th->getMessage());
        }
    }

    public function permanentDelete($id)
    {
        try {
            $user = User::onlyTrashed()->where('id', $id)->forceDelete();

            return ApiFormatter::sendResponse(200, true, "Berhasil hapus permanen data yang telah dihapus!", ['id' => $id]);
        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silakan coba lagi!", $th->getMessage());
        }
    }

    public function permanentDeleteAll()
    {
        try {
            $user = User::onlyTrashed();

            $user->forceDelete();

            return ApiFormatter::sendResponse(200, true, "Berhasil hapus permanen data yang telah dihapus!");
        } catch (\Throwable $th) {
            //throw $th;
            return ApiFormatter::sendResponse(404, false, "Proses gagal! silakan coba lagi!", $th->getMessage());
        }
    }

    public function __construct()
    {
        $this->middleware('auth:api');
    }
}
