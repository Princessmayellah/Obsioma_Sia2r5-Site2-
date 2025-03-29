<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserJob; // Added UserJob model import
use DB;
use App\Traits\ApiResponser;

class UserController extends Controller {
    use ApiResponser;

    private $request;

    public function __construct(Request $request){
        $this->request = $request;
    }

    public function getUsers(){
        // Using DB facade to fetch users from the database
        $users = DB::connection('mysql')
            ->select("SELECT * FROM tbl_user_site2");

        return $this->successResponse($users); 
    }

    /**
     * Display a listing of users.
     *
     * @return Illuminate\Http\Response
     */
    public function index(){
        $users = User::all();
        return $this->successResponse($users);
    }

    /**
     * Add a new user.
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\Response
     */
    public function add(Request $request){
        $rules = [
            'username' => 'required|max:20',
            'password' => 'required|max:20',
            'gender' => 'required|in:Male,Female',
            'jobid' => 'required|numeric|min:1|not_in:0', // Added jobid validation
        ];

        $this->validate($request, $rules);
        
         // Validate if jobid exists in tbluserjob
         try {
            UserJob::findOrFail($request->jobid);
        } catch (\Exception $e) {
            return $this->errorResponse('Invalid job ID provided', Response::HTTP_BAD_REQUEST);
        }

        $user = User::create($request->all());
        return $this->successResponse($user, Response::HTTP_CREATED);
    }

    /**
     * Show the specified user by ID.
     *
     * @param  int  $id
     * @return Illuminate\Http\Response
     */
    public function show($id) {
        $user = User::findOrFail($id);
    
        if (!$user) {
            return $this->errorResponse('User not found', Response::HTTP_NOT_FOUND);
        }
    
        return $this->successResponse($user);
    }
    

    /**
     * Update the specified user by ID.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  int  $id
     * @return Illuminate\Http\Response
     */
    public function update(Request $request, $id){
        $rules = [
            'username' => 'max:20',
            'password' => 'max:20',
            'gender' => 'in:Male,Female',
            'jobid' => 'required|numeric|min:1|not_in:0', // Added jobid validation
        ];

        $this->validate($request, $rules);

        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('User not found', Response::HTTP_NOT_FOUND);
        }

        $user->fill($request->all());

        // Check if any changes were made
        if ($user->isClean()) {
            return $this->errorResponse('At least one value must change', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->save();
        return $this->successResponse($user);
    }

    /**
     * Remove the specified user by ID.
     *
     * @param  int  $id
     * @return Illuminate\Http\Response
     */
    public function delete($id){
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('User not found', Response::HTTP_NOT_FOUND);
        }

        $user->delete();
        return $this->successResponse(['message' => 'User deleted successfully'], Response::HTTP_OK);
    }
}
