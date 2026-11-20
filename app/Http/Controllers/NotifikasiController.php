<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The signed-in account's notifications, for every role. Lookups go through
 * the account's own notifications, so nobody can read or mark someone else's.
 */
class NotifikasiController extends Controller
{
    public function index(Request $request): View
    {
        return view('notifikasi', [
            'daftarNotifikasi' => $request->user()->notifications()->paginate(15),
            'belumDibaca' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Marks one as read and opens the page it is about.
     */
    public function buka(Request $request, string $notifikasi): RedirectResponse
    {
        $item = $request->user()->notifications()->findOrFail($notifikasi);
        $item->markAsRead();

        $tautan = (string) ($item->data['tautan'] ?? '');

        // Only follow links into this application.
        return str_starts_with($tautan, url('/')) ? redirect()->to($tautan) : redirect()->route('notifikasi.index');
    }

    public function bacaSemua(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return redirect()->route('notifikasi.index')->with('status', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
