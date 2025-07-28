<?php

namespace App\Http\Controllers\User;

use App\Actions\Submission\DeleteSubmissionAction;
use App\Actions\Submission\GetSubmissionAction;
use App\Actions\Submission\SearchSubmissionAction;
use App\Actions\Submission\UpdateSubmissionAction;
use App\Actions\Submission\UpdateSubmissionStatusAction;
use App\Actions\SubmissionLog\SearchSubmissionLogAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function __construct(
        protected SearchSubmissionAction $searchSubmissionAction,
        protected GetSubmissionAction $getSubmissionAction,
        protected UpdateSubmissionAction $updateSubmissionAction,
        protected DeleteSubmissionAction $deleteSubmissionAction,
        protected SearchSubmissionLogAction $searchSubmissionLogAction,
        protected UpdateSubmissionStatusAction $updateSubmissionStatusAction
    ) {}

    public function logout(Request $request)
    {
        $user = $request->user();
        // $user->tokens()->delete();
        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ]);
    }

    public function getAuthUser(Request $request)
    {
        return $request->user();
    }
}
