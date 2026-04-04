<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    /**
     * Tampilkan halaman full list notifikasi
     */
    public function index()
    {
        $notifications = auth()->user()->notifications()
                            ->orderBy('created_at', 'desc')
                            ->paginate(15);
                            
        return view('notification.index', compact('notifications'));
    }

    /**
     * Tandai sebuah notifikasi telah dibaca
     */
    public function markAsRead(Request $request, $id)
    {
        $notif = auth()->user()->notifications()->findOrFail($id);
        
        if (!$notif->is_read) {
            $notif->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }

        // Kalau ada URL redirect di dalam pesan atau route khusus,  bisa dialihkan.
        // Saat ini redirect back.
        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->back();
    }

    /**
     * Tandai semua notifikasi milik user ini telah dibaca
     */
    public function markAllAsRead(Request $request)
    {
        auth()->user()->notifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    /**
     * Hapus spesifik notifikasi
     */
    public function destroy($id)
    {
        $notif = auth()->user()->notifications()->findOrFail($id);
        $notif->delete();

        return redirect()->back()->with('success', 'Notifikasi dihapus.');
    }
}
