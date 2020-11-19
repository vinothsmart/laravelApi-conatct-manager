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
            ]);
        }
    }
}
