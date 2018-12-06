<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//untuk otorisasi halaman 
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{

    /** 
     * Function construct for otorisation and others
    */
    public function __construct()
    {
        $this->middleware(function($request, $next)
        {

            if(Gate::allows('manage-users')) return $next($request);
          
            abort(403, 'Anda tidak memiliki cukup hak akses');
        });
        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        /**
         * Mendapatkan data user dari database 
         * menggunakan model User dan menampilkan 
         * per halaman 10 users dengan method paginate()
         */
        $users = \App\User::paginate(10);
        
        $filterKeyword  = $request->get('keyword');
        $status         = $request->get('status');

        if($status){
            $users = \App\User::where('status', $status)->paginate(10);
        } else {
            $users = \App\User::paginate(10);
        }

        if($filterKeyword){
            if ($status) {
                $users = \App\User::where('email', 'LIKE', "%$filterKeyword%")
                ->where('status', $status)
                ->paginate(10);
            } else {
                $users = \App\User::where('email', 'LIKE', "%$filterKeyword%")
                ->paginate(10);
            }
        }

        //mengembalikan view beserta data user
        return view('users.index', ['users' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("users.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        \Validator::make($request->all(),[
            "name" => "required|min:5|max:100",
            "username" => "required|min:5|max:20",
            "roles"    => "required",
            "phone"    => "required|digits_between:10,12",
            "address"  => "required|min:20|max:200",
            "avatar"   => "required",
            "email"    => "required|email",
            "password" => "required",
            "password_confirmation" => "required|same:password"
        ])->validate();

        //Membuat instance dari model User dengan kode ini
        $new_user = new \App\User;

        /**
         * Meng set properti dari user dengan nilai yang 
         * berasal dari data yang dikirim oleh form create
         * user seperti ini:
         */
        $new_user->name         = $request->get('name');
        $new_user->username     = $request->get('username');
        $new_user->roles        = json_encode($request->get('roles'));
        $new_user->name         = $request->get('name');
        $new_user->address      = $request->get('address');
        $new_user->phone        = $request->get('phone');
        $new_user->email        = $request->get('email');
        $new_user->password     = \Hash::make($request->get('password'));

        /**
         * Kode di atas mengecek apakah request memiliki file dengan nama avatar, jika ada maka simpan file tersebut menggunakan method $request->file()->store().
         * store()akan menghasilkan path dari file yang kita simpan. Sehingga kode diatas akan menghasilkan string path dimana lokasi file berada
         * store('avatars', 'public'); --> disimpan dalam folder avatar dan kita set visibilitynya menjadi public agar bisa diakses oleh siapa saja yang menggunakan url
         */
        if ($request->file('avatar')) {
            $file = $request->file('avatar')->store('avatars','public');
            $new_user->avatar = $file;
        }

        //Menyimpan user baru 
        $new_user->save();
        
        //Redirect ke halaman form create dengan menampilkan status berhasil
        return redirect()->route('users.create')->with('status', 'User successfulyy created.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = \App\User::findOrFail($id);

        return view('users.show', ['user' => $user]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = \App\User::findOrFail($id);

        return view('users.edit', ['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        \Validator::make($request->all(), [
            "name" => "required|min:5|max:100",
            "roles" => "required",
            "phone" => "required|digits_between:10,12",
            "address" => "required|min:20|max:200",
          ])->validate();

        $user = \App\User::findOrFail($id);
        $user->name = $request->get('name');
        $user->roles = json_encode($request->get('roles'));
        $user->address = $request->get('address');
        $user->phone = $request->get('phone');
        $user->status = $request->get('status');
        if($request->file('avatar')){
            if($user->avatar && file_exists(storage_path('app/public/' . $user->avatar))){
                \Storage::delete('public/'.$user->avatar);
            }
            $file = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $file;
        }
        $user->save();
        return redirect()->route('users.edit', ['id' => $id])->with('status', 'User succesfully updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = \App\User::findOrFail($id);
        $user->delete();
        return redirect()->route('users.index')->with('status','User successfully deleted');
    }
}
