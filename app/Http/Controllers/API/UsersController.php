<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only('show');
    }

    public function show( Request $request ){
        return new UserResource(auth()->user());
    }

    public function update(Request $request, User $user)
    {
        $this->validate($request, [
            'name' => 'required|min:3|max:50|string',
            'email' => 'required|email',
            'new_password' => 'required_with:new_password',
            'existing_password' => 'sometimes|string|min:6',
            'confirm_password' => 'sometimes|same:new_password'
        ]);

        $user = $request->user();

        if ($user->avatar != $request['avatar']) {
            if (Storage::disk('public')->exists('/avatar/'.$user->avatar)) {
                if ($user->avatar !== 'avatar.png') {
                    Storage::disk('public')->delete('/avatar/'.$user->avatar);
                }

                $image = $request->file('avatar');
                $image_path = $image->getPathname();
                $filename = time().'_'.preg_replace('/\s+/', '_', strtolower(self::translit($image->getClientOriginalName())));
                $tmp = $image->storeAs('/avatar', $filename, 'public');

                $user->avatar = $filename;
            }
        }

        if (Hash::check($request['existing_password'], $user->password)) {
            $user->password = bcrypt($request['new_password']);
        }

        $user->name = $request['name'];
        $user->email = $request['email'];
        $user->save();

        if ($user) {
            return response()->json($user, 200);

        } else {
            return response()->json($user, 500);
        }
    }

    public static function translit($str)
    {
        $tr = array(
            "??"=>"A","??"=>"B","??"=>"V","??"=>"G",
            "??"=>"D","??"=>"E","??"=>"J","??"=>"Z","??"=>"I",
            "??"=>"Y","??"=>"K","??"=>"L","??"=>"M","??"=>"N",
            "??"=>"O","??"=>"P","??"=>"R","??"=>"S","??"=>"T",
            "??"=>"U","??"=>"F","??"=>"H","??"=>"TS","??"=>"CH",
            "??"=>"SH","??"=>"SCH","??"=>"","??"=>"YI","??"=>"",
            "??"=>"E","??"=>"YU","??"=>"YA","??"=>"a","??"=>"b",
            "??"=>"v","??"=>"g","??"=>"d","??"=>"e","??"=>"j",
            "??"=>"z","??"=>"i","??"=>"y","??"=>"k","??"=>"l",
            "??"=>"m","??"=>"n","??"=>"o","??"=>"p","??"=>"r",
            "??"=>"s","??"=>"t","??"=>"u","??"=>"f","??"=>"h",
            "??"=>"ts","??"=>"ch","??"=>"sh","??"=>"sch","??"=>"y",
            "??"=>"yi","??"=>"'","??"=>"e","??"=>"yu","??"=>"ya",
            " "=>"_","?"=>"_","/"=>"_","\\"=>"_",
            "*"=>"_",":"=>"_","*"=>"_","\""=>"_","<"=>"_",
            ">"=>"_","|"=>"_"
        );
        return strtr($str,$tr);
    }

//    public function getUser(){
//        return Auth::guard('api')->user();
//    }
}
