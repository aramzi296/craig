<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\WhatsappSession;
use Illuminate\Http\Request;

class BotActionController extends Controller
{
    public function deleteDraft($id, $token)
    {
        $session = WhatsappSession::where('payload->token', $token)->first();
        
        if (!$session) {
            return view('bot.action-result', [
                'success' => false,
                'message' => 'Tautan tidak valid atau sudah kedaluwarsa.'
            ]);
        }

        $listing = Listing::where('id', $id)
            ->where('is_draft', true)
            ->first();

        if (!$listing) {
            return view('bot.action-result', [
                'success' => false,
                'message' => 'Postingan tidak ditemukan atau sudah dihapus.'
            ]);
        }

        // Verify that the listing belongs to the user associated with this session phone number
        $user = \App\Models\User::where('whatsapp', $session->phone_number)->first();
        if (!$user || $listing->user_id !== $user->id) {
            return view('bot.action-result', [
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus draf ini.'
            ]);
        }

        $listing->delete();

        return view('bot.action-result', [
            'success' => true,
            'message' => 'Draf postingan "' . $listing->title . '" berhasil dihapus. Silakan kembali ke WhatsApp untuk membuat postingan baru.'
        ]);
    }
}
