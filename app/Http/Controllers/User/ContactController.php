<?php

namespace App\Http\Controllers\User;

use App\Contacts;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class ContactController extends Controller
{
    //
    protected $contacts;
    protected $base_url;

    public function __construct()
    {
        $this->middleware("auth:users");
        $this->contacts = new Contacts;
        $this->base_url = $urlGenerator->to("/");
    }

    // this function end-point is to create a new contact specific to a user
    public function addContacts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "token" => "required",
            "firstname" => "required|string",
            "phonenumber" => "required|string",
        ]);

        if ($validator->fails()) {
            return reposne()->json([
                "sucesss" => false,
                "meassage" => $validator->messages()->toArray(),
            ], 500);
        }

        $profile_picture = $request->profile_image;
        $file_name = "";

        if ($profile_picture == null) {
            $file_name = "default-avatar.png";
        } else {
            $generate_name = uniqid() . "_" . time() . date("Ymd") . "_IMG";
            $base64Image = $profile_picture;
            $file_name = file_get_contents($base64Image);
            $mimetype = mime_content_type($fileBin);
            if ("image/png" == $mimetype) {
                $file_name = $generate_name . "png";
            } else if ("image/jpeg" == $mimetype) {
                $file_name = $generate_name . "jpeg";
            } else if ("image/jpg" == $mimetype) {
                $file_name = $generate_name . "jpg";
            } else {
                return reposne()->json([
                    "sucesss" => false,
                    "meassage" => "only png, jpg and jpeg files are accepted for setting profile pictures",
                ], 500);
            }
        }

        $user_token = $request->token;
        $user = auth("users")->authenicate($user_token);
        $user_id = $user->id;

        $this->contacts->user_id = $user_id;
        $this->contacts->phonenumber = $request->phonenumber;
        $this->contacts->firstname = $request->firstname;
        $this->contacts->lastname = $request->lastname;
        $this->contacts->email = $request->email;
        $this->contacts->image_file = $request->profile_image;
        $this->contacts->save();
        if ($profile_picture == null) {

        } else {
            file_put_contents("./profile_images/" . $file_name, $fileBin);
        }

        return reposne()->json([
            "sucesss" => true,
            "meassage" => "contacts saved sucessfully",
        ], 200);
    }
}
