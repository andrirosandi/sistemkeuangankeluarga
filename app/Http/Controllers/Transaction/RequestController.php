<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\RequestHeader;
use App\Models\RequestDetail;
use App\Models\TemporaryMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\RoleVisibility;

class RequestController extends Controller
{
    private function getTransCode($type) {
        return $type === 'out' ? 2 : 1;
    }

    private function getTypeLabel($type) {
        return $type === 'out' ? 'Kas Keluar' : 'Kas Masuk';
    }

    protected function notifyApprovers($req, $type)
    {
        $permissionName = $type == 'in' ? 'in.request.approve' : 'out.request.approve';
        $approvers = \App\Models\User::permission($permissionName)->get();
        
        $notifications = [];
        foreach($approvers as $approver) {
            $notifications[] = [
                'user_id' => $approver->id,
                'message' => 'Pengajuan baru menunggu persetujuan: <strong>' . htmlspecialchars($req->description) . '</strong> dari ' . auth()->user()->name,
                'is_read' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (!empty($notifications)) {
            \DB::table('notifications')->insert($notifications);
        }
    }

    public function index(Request $request, $type)
    {
        $transCode = $this->getTransCode($type);
        $title = "Pengajuan " . $this->getTypeLabel($type);
        
        $user = auth()->user();
        
        // Visibility logic
        $visibleUserIds = RoleVisibility::getVisibleUserIds($user);

        $query = RequestHeader::with(['creator', 'category'])
            ->where('trans_code', $transCode)
            ->whereIn('created_by', $visibleUserIds);

        // Filters
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('request_date', [$request->date_from, $request->date_to]);
        }

        // Default sorting: urgent priority first, then date
        $query->orderByRaw("FIELD(status, 'requested', 'draft', 'approved', 'rejected', 'canceled'),
                           FIELD(priority, 'high', 'normal', 'low'),
                           created_at DESC");

        $requests = $query->get();

        return view('transaction.request.index', compact('requests', 'title', 'type'));
    }

    public function create($type)
    {
        $title = "Buat Pengajuan " . $this->getTypeLabel($type);
        $transCode = $this->getTransCode($type);
        $categories = Category::orderBy('name')->get();

        return view('transaction.request.form', compact('title', 'type', 'categories'));
    }

    public function store(Request $request, $type)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'request_date' => 'required|date',
            'priority' => 'required|in:low,normal,high',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric|min:0',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'integer',
        ]);

        try {
            DB::beginTransaction();

            $transCode = $this->getTransCode($type);
            $totalAmount = collect($request->items)->sum('amount');

            $status = $request->input('action_type', 'draft') === 'requested' ? 'requested' : 'draft';

            // 1. Create Header
            $header = RequestHeader::create([
                'category_id' => $request->category_id,
                'request_date' => $request->request_date,
                'trans_code' => $transCode,
                'priority' => $request->priority,
                'description' => $request->description,
                'notes' => $request->notes,
                'amount' => $totalAmount,
                'status' => $status,
                'created_by' => auth()->id(),
            ]);

            // 2. Create Details
            foreach ($request->items as $item) {
                RequestDetail::create([
                    'header_id' => $header->id,
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                ]);
            }

            // 3. Attach Media
            if ($request->media_ids) {
                $tempMedia = TemporaryMedia::whereIn('id', $request->media_ids)
                    ->where('user_id', auth()->id())
                    ->get();

                foreach ($tempMedia as $temp) {
                    $mediaItems = $temp->getMedia('temp');
                    foreach ($mediaItems as $media) {
                        $media->move($header, 'requests');
                    }
                    $temp->delete(); // Clean up temp
                }
            }

            DB::commit();

            if ($status === 'requested') {
                $this->notifyApprovers($header, $type);
                $msg = 'Pengajuan berhasil disimpan dan langsung diajukan.';
            } else {
                $msg = 'Pengajuan berhasil disimpan sebagai Draft.';
            }

            return redirect()->route("{$type}.request.index")->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan pengajuan: ' . $e->getMessage());
        }
    }

    public function show($type, $id)
    {
        $req = RequestHeader::with(['details', 'category', 'creator', 'approver', 'media'])
            ->findOrFail($id);

        // Cek visibilitas
        $visibleUserIds = RoleVisibility::getVisibleUserIds(auth()->user());
        if (!$visibleUserIds->contains($req->created_by)) {
            abort(403, 'Akses ditolak.');
        }

        $title = "Detail Pengajuan " . $this->getTypeLabel($type);
        $requestData = $req;
        return view('transaction.request.show', compact('requestData', 'title', 'type'));
    }

    public function edit($type, $id)
    {
        $req = RequestHeader::with(['details', 'media'])->findOrFail($id);

        if ($req->status !== 'draft') {
            return redirect()->route("{$type}.request.index")->with('error', 'Hanya pengajuan Draft yang dapat diedit.');
        }
        
        $visibleUserIds = RoleVisibility::getVisibleUserIds(auth()->user());
        if (!$visibleUserIds->contains($req->created_by)) {
            abort(403, 'Akses ditolak.');
        }

        $title = "Edit Pengajuan " . $this->getTypeLabel($type);
        $categories = Category::orderBy('name')->get();
        $requestData = $req;

        return view('transaction.request.form', compact('title', 'type', 'categories', 'requestData'));
    }

    public function update(Request $request, $type, $id)
    {
        $req = RequestHeader::findOrFail($id);
        
        if ($req->status !== 'draft') {
            return redirect()->route("{$type}.request.index")->with('error', 'Hanya pengajuan Draft yang dapat diedit.');
        }

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'request_date' => 'required|date',
            'priority' => 'required|in:low,normal,high',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric|min:0',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'integer',
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = collect($request->items)->sum('amount');

            $status = $request->input('action_type', 'draft') === 'requested' ? 'requested' : 'draft';

            $req->update([
                'category_id' => $request->category_id,
                'request_date' => $request->request_date,
                'priority' => $request->priority,
                'description' => $request->description,
                'notes' => $request->notes,
                'amount' => $totalAmount,
                'status' => $status,
            ]);

            // Delete old details
            $req->details()->delete();

            // Recreate details
            foreach ($request->items as $item) {
                RequestDetail::create([
                    'header_id' => $req->id,
                    'description' => $item['description'],
                    'amount' => $item['amount'],
                ]);
            }

            // Media attachment
            if ($request->media_ids) {
                $tempMedia = TemporaryMedia::whereIn('id', $request->media_ids)
                    ->where('user_id', auth()->id())
                    ->get();
                foreach ($tempMedia as $temp) {
                    $mediaItems = $temp->getMedia('temp');
                    foreach ($mediaItems as $media) {
                        $media->move($req, 'requests');
                    }
                    $temp->delete();
                }
            }

            DB::commit();

            if ($status === 'requested') {
                $this->notifyApprovers($req, $type);
                $msg = 'Draft pengajuan berhasil diupdate dan diajukan.';
            } else {
                $msg = 'Draft pengajuan berhasil diupdate.';
            }

            return redirect()->route("{$type}.request.index")->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate pengajuan: ' . $e->getMessage());
        }
    }

    public function submit(Request $request, $type, $id)
    {
        $req = RequestHeader::findOrFail($id);

        if ($req->status !== 'draft') {
            return redirect()->route("{$type}.request.index")->with('error', 'Hanya pengajuan Draft yang dapat disubmit.');
        }

        try {
            DB::beginTransaction();
            $req->update(['status' => 'requested']);
            
            // TODO: Create logic to insert into notifications table for authorized approvers
            $this->notifyApprovers($req, $type);
            
            DB::commit();
            return redirect()->route("{$type}.request.index")->with('success', 'Pengajuan berhasil disubmit. Menunggu persetujuan Admin.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal submit pengajuan: ' . $e->getMessage());
        }
    }

    public function destroy($type, $id)
    {
        $req = RequestHeader::findOrFail($id);

        if (!in_array($req->status, ['draft', 'canceled'])) {
            return redirect()->route("{$type}.request.index")->with('error', 'Hanya pengajuan Draft atau Canceled yang dapat dihapus.');
        }
        
        $visibleUserIds = RoleVisibility::getVisibleUserIds(auth()->user());
        if (!$visibleUserIds->contains($req->created_by)) {
            abort(403, 'Akses ditolak.');
        }

        try {
            $req->delete();
            return redirect()->route("{$type}.request.index")->with('success', 'Pengajuan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus pengajuan.');
        }
    }
}
