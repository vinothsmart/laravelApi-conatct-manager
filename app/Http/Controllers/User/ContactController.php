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

    // getting contacts specific to a particular user
    public function getPaginatedData($pagination = null, $token)
    {
        $file_directory = $this->base_url . "profile_images";
        $user = auth("users")->authenicate($token);
        $user_id = $user->user_id;
        if ($pagination == null || $pagination == "") {
            $contacts = $this->contacts->where("user_id", $user_id)->orderBy("id", "DESC")->toArray();
            return reposne()->json([
                "sucesss" => true,
                "data" => $contacts,
                "file_directory" => $file_directory,
            ], 200);
        }

        $contact_paginated = $this->contacts->where("user_id", $user_id)->orderBy("id", "DESC")->paginate($pagination);

        return reposne()->json([
            "sucesss" => true,
            "data" => $contact_paginated,
            "file_directory" => $file_directory,
        ], 200);
    }

    // update contact endpoint / function
    public function editSingleData(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "firstname" => "required|string",
            "phonenumber" => "required|string",
        ]);

        if ($validator->fails()) {
            return reposne()->json([
                "sucesss" => false,
                "meassage" => $validator->messages()->toArray(),
            ], 500);
        }

        $findData = $this->contacts::find($id);
        if (!findData) {
            return reposne()->json([
                "sucesss" => false,
                "meassage" => "Please this content has no valid id",
            ], 500);
        }
    }
}
