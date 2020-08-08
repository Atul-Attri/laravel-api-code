<?php

namespace App\Http\Controllers\API;

use DB;
use Auth;
use App\User;
use Exception;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    /**
     * List other users other then authenticated user
     */
    public function list(Request $request)
    {
        $request->validate([
            'gender,' => 'nullable|string',
            'permanent_address.city' => 'nullable|string|max:20',
        ]);

        $query =  User::with(['permanentAddress' => function ($q) {
            $q->select(['user_id', 'city']);
        }]);

        if($request->filled('permanent_address.city')) {
            $query->whereHas('permanentAddress', function ($q) use ($request) {
                $q->where('city', 'like', '%' . $request->input('permanent_address.city') . '%');
            });
        }

        if($request->filled(['gender'])) {
            $query->where('gender', $request->input('gender'));
        }

        $query->where('id', '<>', $request->user()->id);

        $users = $query->select(['id', 'name', 'gender', 'avatar'])->paginate(15);

        return response()->json([
            'meta' => [
                'status' => 1,
                'code' => 200,
                'message' => 'OK'
            ],
            'data' => $users
        ], 200);
    }

    /**
     * Get profile of a user
     * 
     * @param $id
     */
    public function getProfile(Request $request, $id)
    {
        $response = User::where('id', $id)->with(['permanentAddress', 'companyAddress'])->first();

        return response()->json([
            'meta' => [
                'status' => 1,
                'code' => 200,
                'message' => 'OK'
            ],
            'data' => $response
        ], 200);
    }

    /**
     * Get the authenticated User
     *
     * @return [json]
     */
    public function getMyProfile(Request $request)
    {
        $response = $request->user();
        $response['permanent_address'] = $request->user()->permanentAddress;
        $response['company_address'] = $request->user()->companyAddress;

        return response()->json([
            'meta' => [
                'status' => 1,
                'code' => 200,
                'message' => 'OK'
            ],
            'data' => $response
        ], 200);
    }

    /**
     * Update the authenticated User
     */
    public function updateMyProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'file' => 'nullable|image|max:10000|mimes:jpeg,jpg|png',
            'phone_number' => 'required|regex:/[0-9]{10}/|unique:users,phone_number,'.$request->user()->id,
            'gender' => 'in:male,female,other',
            'date_of_birth' => 'required|date_format:Y-m-d|before:today',
            'permanent_address.street' => 'required|string|max:50',
            'permanent_address.city' => 'required|string|max:20',
            'permanent_address.state' => 'required|string|max:20',
            'permanent_address.country' => 'required|string|max:20',
            'permanent_address.pincode' => 'required|regex:/[0-9]{6}/',
            'company_address.street' => 'required|string|max:50',
            'company_address.city' => 'required|string|max:20',
            'company_address.state' => 'required|string|max:20',
            'company_address.country' => 'required|string|max:20',
            'company_address.pincode' => 'required|regex:/[0-9]{6}/'
        ]);

        // Begin Transaction
        DB::beginTransaction();

        try {
            
            $request->user()->update($request->only([
                'name', 'phone_number', 'gender', 'date_of_birth'
            ]));

            $request->user()->addresses()->updateOrCreate(
                [
                    'type' => 'permanent'
                ],
                $request->only([
                    'permanent_address.street', 'permanent_address.city', 'permanent_address.state',
                    'permanent_address.country', 'permanent_address.pincode'
            ])['permanent_address']);

            $request->user()->addresses()->updateOrCreate(
                [
                    'type' => 'company'
                ],
                $request->only([
                    'company_address.street', 'company_address.city', 'company_address.state',
                    'company_address.country', 'company_address.pincode'
            ])['company_address']);

            // Commit Transaction
            DB::commit();

            return response()->json([
                'meta' => [
                    'status' => 1,
                    'code' => 200,
                    'message' => 'Profile updated successfully'
                ]
            ], 200);
        } catch (Exception $e) {
            // Rollback Transaction
            DB::rollback();

            return response()->json([
                'meta' => [
                    'status' => 0,
                    'code' => 409,
                    'message' => $e->getMessage()
                ]
            ], 409);
        }
    }
}