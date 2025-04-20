<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function update_profile(Request $request)
    {
        $request->validate([
            'profileImage' => 'required|image|mimes:jpeg,png,jpg,gif',
        ]);

        $user = auth()->user();
        $username = $user->username;

        // Define the storage path
        $imagePath = public_path('../public/storage/profile_pics/');
        $imageName = $username . '.jpg';

        // Check if the file already exists and delete it
        if (file_exists($imagePath . $imageName)) {
            unlink($imagePath . $imageName);
        }

        // Move the uploaded file to the target directory
        $request->profileImage->move($imagePath, $imageName);

        return response()->json([
            'status' => true,
            'message' => 'Profile image updated successfully',
        ]);
    }
}