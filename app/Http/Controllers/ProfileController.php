<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
 
class ProfileController extends Controller
{
    protected $imageService;

    public function __construct(\App\Services\ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function edit()
    {
        $user = auth()->user();
        return view('profile.edit', compact('user'));
    }
 
    public function update(Request $request)
    {
        $user = auth()->user();
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);
 
        if ($request->hasFile('photo')) {
            // Delete old photo from ImageKit if exists
            if ($user->ik_file_id) {
                $this->imageService->deleteFileById($user->ik_file_id);
            }

            $uploadResult = $this->imageService->uploadProfilePhoto($request->file('photo'), $user->id);
            $data['profile_photo'] = $uploadResult['path'];
            $data['ik_file_id'] = $uploadResult['fileId'];
        }

        $user->update($data);
 
        return back()->with('success', 'Profil Anda berhasil diperbarui.');
    }
}
