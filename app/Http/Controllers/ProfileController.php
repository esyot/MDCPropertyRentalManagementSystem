<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Notification;
use App\Models\Message;
use App\Models\User;
use Auth;
use Hash;
use Str;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        $setting = Setting::find(1);
        $current_user_name = Auth::user()->name;
        $page_title = "Profile";
       
        $messages = Message::where('receiver_name', $current_user_name)->where('isRead', false)->get();
        $unreadMessages = $messages->count();

        $contacts = Message::where('receiver_name', $current_user_name)
            ->latest()
            ->get()
            ->groupBy('sender_name')
            ->map(fn($group) => $group->first())
            ->values();

        $users = User::all();

        $roles = Auth::user()->getRoleNames();

        $categories = [];
        $unreadNotifications = 0;
        $notifications = [];
        $currentCategory = null;

        if ($roles->contains('superadmin')) {

            $notifications = Notification::whereIn('for', ['superadmin', 'all'])->whereJsonDoesntContain(
                'isDeletedBy',
                Auth::user()->id
            )->orderBy('created_at', 'DESC')->get();

            $unreadNotifications = Notification::whereJsonDoesntContain(
                'isReadBy',
                Auth::user()->id
            )->whereJsonDoesntContain('isDeletedBy', Auth::user()->id)->whereIn('for', ['admin', 'both'])->count();


        } else if ($roles->contains('admin')) {

            $notifications = Notification::whereIn('for', ['admin', 'both'])->whereJsonDoesntContain(
                'isDeletedBy',
                Auth::user()->id
            )->orderBy('created_at', 'DESC')->get();
            $unreadNotifications = Notification::whereJsonDoesntContain(
                'isReadBy',
                Auth::user()->id
            )->whereJsonDoesntContain('isDeletedBy', Auth::user()->id)->whereIn('for', ['admin', 'both'])->count();



        } else if ($roles->contains('staff')) {

            $notifications = Notification::where(function ($query) use ($categoryIds) {
                $query->whereIn('category_id', $categoryIds)
                    ->orWhereNull('category_id');
            })->whereIn('for', ['staff', 'both'])
                ->orderBy('created_at', 'DESC')
                ->get();

            $unreadNotifications = Notification::whereJsonDoesntContain('isReadBy', Auth::user()->id)->where(function ($query) use ($categoryIds) {
                $query->whereIn('category_id', $categoryIds)
                    ->orWhereNull('category_id');
            })->whereIn('for', ['staff', 'both'])
                ->orderBy('created_at', 'DESC')
                ->get()->count();

        }

        return view('admin.pages.profile', compact('users', 'unreadMessages', 'contacts', 'notifications', 'unreadNotifications', 'setting', 'page_title'));

    }

    public function profileUpdate(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'string',
            'img' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'email' => 'email'
        ]);

        $user = User::find(Auth::user()->id);

        // Handle the uploaded file
        if ($request->hasFile('img')) {
            $image = $request->file('img');

            // Generate a unique filename for the image
            $imageFileName = Str::random(10) . '.' . $image->getClientOriginalExtension();

            // Define the path for storing the image
            $filePath = 'images/users/';

            // Store the image file
            $image->storeAs($filePath, $imageFileName, 'public');

            $user->update([
                'img' => $imageFileName
            ]);
            return redirect()->back()->with('success', 'User has been updated successfully!');

        } else {




            $user->update([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],

            ]);

            return redirect()->back()->with('success', 'User has been updated successfully!');
        }
    }

    public function passwordUpdate(Request $request)
    {
        $user = Auth::user();

        // Validate the input based on whether the password has been changed before
        if ($user->isPasswordChanged) {
            $validatedData = $request->validate([
                'current_password' => ['required', 'string'],
                'new_password' => ['required', 'string', 'min:8'],
                'confirm_password' => ['required', 'string', 'min:8', 'same:new_password'],
            ]);

            // Verify the current password
            if (Hash::check($validatedData['current_password'], $user->password)) {
                $user->update([
                    'password' => Hash::make($validatedData['confirm_password']),
                ]);

                return redirect()->back()->with('success', 'Password has been updated successfully!');
            }

            $errorMessage = 'Current password is incorrect';
        } else {
            $validatedData = $request->validate([
                'new_password' => ['required', 'string', 'min:8'],
                'confirm_password' => ['required', 'string', 'min:8', 'same:new_password'],
            ]);

            $user->update([
                'password' => Hash::make($validatedData['confirm_password']),
                'isPasswordChanged' => 1,
            ]);

            return redirect()->back()->with('success', 'Password has been set successfully!');
        }

        // Handle error responses
        if (isset($errorMessage)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 401);
            }

            return redirect()->back()->withErrors(['current_password' => $errorMessage])->withInput();
        }
    }



}
