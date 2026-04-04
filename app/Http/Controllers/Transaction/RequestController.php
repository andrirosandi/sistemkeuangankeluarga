<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\RequestHeader;
use App\Models\RequestDetail;
use App\Models\TransactionHeader;
use App\Models\TransactionDetail;
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

        $keyword = 'Pengajuan baru menunggu persetujuan: <strong>' . htmlspecialchars($req->description) . '</strong>';
        $notifications = [];
        foreach($approvers as $approver) {
            $exists = \DB::table('notifications')
                ->where('user_id', $approver->id)
                ->where('message', 'like', '%' . addcslashes($req->description, '%_') . '%')
                ->where('is_read', 0)
                ->exists();

            if (!$exists) {
                $notifications[] = [
                    'user_id' => $approver->id,
                    'message' => $keyword . ' dari ' . auth()->user()->name,
                    'is_read' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        if (!empty($notifications)) {
            \DB::table('notifications')->insert($notifications);
        }
    }

    protected function notifyUser($userId, $message)
    {
        \DB::table('notifications')->insert([
            'user_id' => $userId,
            'message' => $message,
            'is_read' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Approve a request: set status approved, auto-create draft transaction.
     */
    public function approve(Request $request, $type, $id)
    {
        $req = RequestHeader::with('details')->findOrFail($id);

        if ($req->status !== 'requested') {
            return redirect()->route("{$type}.request.index")->with('error', 'Hanya pengajuan berstatus Requested yang dapat disetujui.');
        }

        try {
            DB::beginTransaction();

            // 1. Update request status
            $req->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // 2. Auto-create transaction header (draft)
            $transaction = TransactionHeader::create([
                'category_id' => $req->category_id,
                'description' => $req->description,
                'notes' => $req->notes,
                'amount' => $req->amount,
                'request_id' => $req->id,
                'trans_code' => $req->trans_code,
                'transaction_date' => $req->request_date,
                'created_by' => auth()->id(),
                'status' => 'draft',
            ]);

            // 3. Copy request details → transaction details
            foreach ($req->details as $detail) {
                TransactionDetail::create([
                    'header_id' => $transaction->id,
                    'description' => $detail->description,
                    'amount' => $detail->amount,
                    'request_detail_id' => $detail->id,
                ]);

                // Mark request detail as pending realization
                $detail->update(['status' => 'pending']);
            }

            // 4. Notify the requester
            $this->notifyUser(
                $req->created_by,
                'Pengajuan <strong>' . htmlspecialchars($req->description) . '</strong> telah <span class="text-success">disetujui</span> oleh ' . auth()->user()->name . '.'
            );

            DB::commit();
            return redirect()->route("{$type}.request.index")->with('success', 'Pengajuan berhasil disetujui. Draf realisasi telah dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyetujui pengajuan: ' . $e->getMessage());
        }
    }

    /**
     * Reject a request with reason.
     */
    public function reject(Request $request, $type, $id)
    {
        $req = RequestHeader::findOrFail($id);

        if ($req->status !== 'requested') {
            return redirect()->route("{$type}.request.index")->with('error', 'Hanya pengajuan berstatus Requested yang dapat ditolak.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        try {
            DB::beginTransaction();

            $req->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            // Notify the requester
            $this->notifyUser(
                $req->created_by,
                'Pengajuan <strong>' . htmlspecialchars($req->description) . '</strong> telah <span class="text-danger">ditolak</span> oleh ' . auth()->user()->name . '. Alasan: ' . htmlspecialchars($request->rejection_reason)
            );

            DB::commit();
            return redirect()->route("{$type}.request.index")->with('success', 'Pengajuan berhasil ditolak.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menolak pengajuan: ' . $e->getMessage());
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
        
        $templates = \App\Models\TemplateHeader::where('trans_code', $transCode)->orderBy('description')->get();

        return view('transaction.request.index', compact('requests', 'title', 'type', 'templates'));
    }

    public function create(Request $request, $type)
    {
        $title = "Buat Pengajuan " . $this->getTypeLabel($type);
        $transCode = $this->getTransCode($type);
        $categories = Category::orderBy('name')->get();
        
        $requestData = null;
        if ($request->has('template_id')) {
            $templateData = \App\Models\TemplateHeader::with('details')->find($request->template_id);
            if ($templateData) {
                // Mock requestData for the form.
                // We use requestData here because the view uses $requestData.
                // We mock it as an stdClass or we just use templateData logic mapping in view.
                // Actually RequestForm view already checks isset($requestData).
                $requestData = new \App\Models\RequestHeader();
                $requestData->category_id = $templateData->category_id;
                $requestData->description = $templateData->description;
                $requestData->priority = 'normal';
                
                // We also need to map details.
                // The form uses $requestData->details.
                $details = collect();
                foreach($templateData->details as $det) {
                    $details->push(new \App\Models\RequestDetail([
                        'description' => $det->description,
                        'amount' => $det->amount
                    ]));
                }
                $requestData->setRelation('details', $details);
            }
        }

        return view('transaction.request.form', compact('title', 'type', 'categories', 'requestData'));
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

            if ($status === 'requested') {
                $this->notifyApprovers($header, $type);
            }

            DB::commit();

            $msg = $status === 'requested'
                ? 'Pengajuan berhasil disimpan dan langsung diajukan.'
                : 'Pengajuan berhasil disimpan sebagai Draft.';

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

        $visibleUserIds = RoleVisibility::getVisibleUserIds(auth()->user());
        if (!$visibleUserIds->contains($req->created_by)) {
            abort(403, 'Akses ditolak.');
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

            if ($status === 'requested') {
                $this->notifyApprovers($req, $type);
            }

            DB::commit();

            $msg = $status === 'requested'
                ? 'Draft pengajuan berhasil diupdate dan diajukan.'
                : 'Draft pengajuan berhasil diupdate.';

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
