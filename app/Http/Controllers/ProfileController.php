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

            // Store file temporarily (Absolute path approach)
            $tempDir = storage_path('app/temp_uploads');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            
            $file = $request->file('photo');
            $fileName = "user_{$user->id}_" . bin2hex(random_bytes(4)) . '.' . $file->getClientOriginalExtension();
            $file->move($tempDir, $fileName);
            $fullPath = $tempDir . DIRECTORY_SEPARATOR . $fileName;

            // Dispatch Job
            \App\Jobs\ProcessProfileImageUpload::dispatch($fullPath, $user->id, $fileName);
        }

        $user->update($data);
 
        return back()->with('success', 'Profil Anda berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = auth()->user();
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        return back()->with('success_password', 'Password Anda berhasil diperbarui.');
    }
}
