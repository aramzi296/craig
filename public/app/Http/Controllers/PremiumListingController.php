<?php

namespace App\Http\Controllers;

use App\Models\PremiumTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PremiumListingController extends Controller
{
    public function confirm(int $listing, int $tariff): \Illuminate\Contracts\View\View
    {
        $listingModel = \App\Models\Listing::where('id', $listing)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $tariffModel = \App\Models\PremiumTariff::where('id', $tariff)
            ->where('is_active', true)
            ->firstOrFail();

        return view('user.premium.confirm', [
            'listing' => $listingModel,
            'tariff' => $tariffModel,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'tariff_id' => 'required|exists:premium_tariffs,id',
        ]);

        $listing = \App\Models\Listing::where('id', $request->listing_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $tariff = \App\Models\PremiumTariff::where('id', $request->tariff_id)
            ->where('is_active', true)
            ->firstOrFail();

        // If there is already an active premium, don't allow another purchase.
        $activeTx = PremiumTransaction::where('user_id', Auth::id())
            ->where('listing_id', $listing->id)
            ->where('listing_type', $listing->type)
            ->where('status', 'active')
            ->whereNotNull('premium_expires_at')
            ->where('premium_expires_at', '>=', now())
            ->orderByDesc('id')
            ->first();

        if ($activeTx) {
            return redirect()->back()->with('error', 'Premium Anda masih aktif.');
        }

        // Check for pending
        $pendingTx = PremiumTransaction::where('user_id', Auth::id())
            ->where('listing_id', $listing->id)
            ->where('listing_type', $listing->type)
            ->where('status', 'pending')
            ->orderByDesc('id')
            ->first();

        if ($pendingTx) {
            return redirect()->route('user.premium.invoice', ['transaction' => $pendingTx->id]);
        }

        $randomAddition = random_int(100, 999);
        $basePrice = (int) $tariff->price;
        $totalPrice = $basePrice + $randomAddition;

        $tx = PremiumTransaction::create([
            'user_id' => Auth::id(),
            'listing_id' => $listing->id,
            'listing_type' => $listing->type,
            'premium_tariff_id' => $tariff->id,
            'base_price' => $basePrice,
            'random_addition' => $randomAddition,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'premium_expires_at' => null,
            'admin_reviewed_at' => null,
        ]);

        return redirect()->route('user.premium.invoice', ['transaction' => $tx->id]);
    }

    public function invoice(int $transaction): \Illuminate\Contracts\View\View
    {
        $tx = PremiumTransaction::with(['premiumTariff', 'listing'])
            ->where('id', $transaction)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('user.premium.invoice', [
            'tx' => $tx,
        ]);
    }

    public function qris(int $transaction): \Illuminate\Contracts\View\View
    {
        $tx = PremiumTransaction::with(['premiumTariff', 'listing'])
            ->where('id', $transaction)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('user.premium.qris', [
            'tx' => $tx,
        ]);
    }

    public function thankYou(int $transaction): \Illuminate\Contracts\View\View
    {
        $tx = PremiumTransaction::with(['premiumTariff', 'listing'])
            ->where('id', $transaction)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('user.premium.thank-you', [
            'tx' => $tx,
        ]);
    }

    public function paid(Request $request, int $transaction): RedirectResponse
    {
        $tx = PremiumTransaction::with(['premiumTariff'])
            ->where('id', $transaction)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // In this simulation, "Saya sudah membayar" = activation immediately.
        if ($tx->status !== 'pending') {
            $backRoute = $tx->listing_type === 'lapak' ? 'user.lapak.listings' : 'user.usaha.listings';
            return redirect()
                ->route($backRoute)
                ->with('warning', 'Transaksi premium tidak dalam status pending.');
        }

        $durationDays = (int) ($tx->premiumTariff?->duration_days ?? 0);
        if ($durationDays <= 0) {
            $backRoute = $tx->listing_type === 'lapak' ? 'user.lapak.listings' : 'user.usaha.listings';
            return redirect()
                ->route($backRoute)
                ->with('error', 'Durasi premium tidak valid.');
        }

        $tx->status = 'waiting_confirmation';
        $tx->premium_expires_at = now()->addDays($durationDays);
        $tx->admin_reviewed_at = null;
        $tx->save();

        // Notify Admin via WhatsApp
        try {
            $whatsapp = new \App\Services\WhatsappService();
            $adminPhone = $whatsapp->adminNumber();
            if ($adminPhone) {
                // Ensure listing relation is loaded
                $tx->load('listing');
                
                $userName = Auth::user()->name ?? 'User';
                $listingTitle = $tx->listing?->title ?? '-';
                $planName = $tx->premiumTariff?->plan_name ?? 'Premium';
                $amount = number_format($tx->total_price, 0, ',', '.');
                
                $msg = "🔔 *Notifikasi Konfirmasi Pembayaran Premium*\n\n" .
                       "👤 User: *{$userName}*\n" .
                       "📂 Listing: *{$listingTitle}*\n" .
                       "💎 Paket: *{$planName}*\n" .
                       "💰 Total: *Rp {$amount}*\n\n" .
                       "Status: *Menunggu Konfirmasi Admin*";
                
                $whatsapp->sendMessage($adminPhone, $msg);
            }
        } catch (\Exception $e) {
            // Silently fail if notification fails, don't block user flow
            \Illuminate\Support\Facades\Log::error('Failed to send admin premium notification: ' . $e->getMessage());
        }

        $backRoute = $tx->listing_type === 'lapak' ? 'user.lapak.listings' : 'user.usaha.listings';
        return redirect()
            ->route($backRoute)
            ->with('success', 'Terima Kasih! Fitur Premium untuk usaha Anda telah aktif. Listing Anda kini akan tampil di posisi teratas.');
    }
}

