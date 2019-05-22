<?php

namespace App\Http\Controllers;

use App\User;
use Cloudder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use libphonenumber\PhoneNumberType;

class UserProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function index(User $user)
    {
        $user = Auth::user();
             
        return response()->json(['data' => [ 'success' => true, 'user' => $user ]], 200);
    }

    public function uploadImage(Request $request)
    {
        $user = Auth::user();

        if ($request->hasFile('image') && $request->file('image')->isValid()){

            $user = $request->file('image');
            $filename = $request->file('image')->getClientOriginalName();
            $image = $request->file('image')->getRealPath();
            Cloudder::upload($image_name, null);

            list($width, $height) = getimagesize($image_name);
            $image_url= Cloudder::show(Cloudder::getPublicId(), ["width" => $width, "height"=>$height]);

            $this->saveImages($request, $image_url);

		$res['message'] = "Upload Successful!";        

        }
        return response()->json($res, 200); 
    }

    public function saveImages(Request $request, $image_url)
    {
        $user = Auth::user();
        $user->image = $image_url;

       $user->save();
    }

    public function editProfile(Request $request)
    {
        $user = Auth::user();
    
        $this->validateRequest($request);

        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->phone = $request->input('phone');
        $user->bio = $request->input('bio');
        $user->dob = $request->input('dob');
        $user->category = $request->input('category');
        if(!empty($request->input('password')))
        {
            $user->password = Hash::make($request->input('password'));
        }
       
        $user->save();
		$res['message'] = "{$user->first_name} Updated Successfully!";        
        return response()->json($res, 200); 
    }

    public function validateRequest(Request $request)
    {
       $id = Auth::id();

       $rules = [
        'first_name' => 'string|required',
        'last_name' => 'string|required',
        'phone' => 'required|phone:NG,US,mobile',
        'bio' => 'required|min:5|max:400',
        'dob' => 'date',
        'category' => 'string',
        'password' => 'nullable|min:6|different:current_password|confirmed',
        ];

        $messages = [
            'required' => ':attribute is required',
            'phone' => ':attribute number is invalid'
        ];

        $this->validate($request, $rules);

    }
}
