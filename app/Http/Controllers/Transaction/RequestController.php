<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\TransactionType;
use App\Http\Requests\Transaction\StoreFinanceRequestRequest;
use App\Http\Requests\Transaction\RejectRequestRequest;
use App\Models\Category;
use App\Models\RequestHeader;
use App\Models\RoleVisibility;
use App\Services\FinanceRequestService;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    use TransactionType;

    public function __construct(
        private FinanceRequestService $requestService,
    ) {}

    public function index(Request $request, $type)
    {
        $transCode = $this->getTransCode($type);
        $title = "Pengajuan " . $this->getTypeLabel($type);

        $user = auth()->user();
        $visibleUserIds = RoleVisibility::getVisibleUserIds($user);

        $query = RequestHeader::with(['creator', 'category'])
            ->where('trans_code', $transCode)
            ->whereIn('created_by', $visibleUserIds);

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
                $requestData = new \App\Models\RequestHeader();
                $requestData->category_id = $templateData->category_id;
                $requestData->description = $templateData->description;
                $requestData->priority = 'normal';

                $details = collect();
                foreach ($templateData->details as $det) {
                    $details->push(new \App\Models\RequestDetail([
                        'description' => $det->description,
                        'amount'      => $det->amount,
                    ]));
                }
                $requestData->setRelation('details', $details);
            }
        }

        return view('transaction.request.form', compact('title', 'type', 'categories', 'requestData'));
    }

    public function store(StoreFinanceRequestRequest $request, $type)
    {
        try {
            $transCode = $this->getTransCode($type);
            $status = $request->input('action_type', 'draft') === 'requested' ? 'requested' : 'draft';

            $this->requestService->createRequest(
                headerData: [
                    'category_id'  => $request->category_id,
                    'request_date' => $request->request_date,
                    'trans_code'   => $transCode,
                    'priority'     => $request->priority,
                    'description'  => $request->description,
                    'notes'        => $request->notes,
                    'status'       => $status,
                    'created_by'   => auth()->id(),
                ],
                items: $request->items,
                mediaIds: $request->media_ids,
                userId: auth()->id(),
            );

            $msg = $status === 'requested'
                ? 'Pengajuan berhasil disimpan dan langsung diajukan.'
                : 'Pengajuan berhasil disimpan sebagai Draft.';

            return redirect()->route("{$type}.request.index")->with('success', $msg);
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan pengajuan. Silakan coba lagi.');
        }
    }

    public function show($type, $id)
    {
        $req = RequestHeader::with(['details', 'category', 'creator', 'approver', 'media'])
            ->findOrFail($id);

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

    public function update(StoreFinanceRequestRequest $request, $type, $id)
    {
        $req = RequestHeader::findOrFail($id);

        if ($req->status !== 'draft') {
            return redirect()->route("{$type}.request.index")->with('error', 'Hanya pengajuan Draft yang dapat diedit.');
        }

        $visibleUserIds = RoleVisibility::getVisibleUserIds(auth()->user());
        if (!$visibleUserIds->contains($req->created_by)) {
            abort(403, 'Akses ditolak.');
        }

        try {
            $status = $request->input('action_type', 'draft') === 'requested' ? 'requested' : 'draft';

            $this->requestService->updateRequest(
                req: $req,
                headerData: [
                    'category_id'  => $request->category_id,
                    'request_date' => $request->request_date,
                    'priority'     => $request->priority,
                    'description'  => $request->description,
                    'notes'        => $request->notes,
                    'status'       => $status,
                ],
                items: $request->items,
                mediaIds: $request->media_ids,
                userId: auth()->id(),
            );

            $msg = $status === 'requested'
                ? 'Draft pengajuan berhasil diupdate dan diajukan.'
                : 'Draft pengajuan berhasil diupdate.';

            return redirect()->route("{$type}.request.index")->with('success', $msg);
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->withInput()->with('error', 'Gagal mengupdate pengajuan. Silakan coba lagi.');
        }
    }

    public function approve(Request $request, $type, $id)
    {
        $req = RequestHeader::with('details')->findOrFail($id);

        if ($req->status !== 'requested') {
            return redirect()->route("{$type}.request.index")->with('error', 'Hanya pengajuan berstatus Requested yang dapat disetujui.');
        }

        if ($req->created_by === auth()->id()) {
            return redirect()->route("{$type}.request.index")->with('error', 'Anda tidak dapat menyetujui pengajuan sendiri.');
        }

        try {
            $this->requestService->approveRequest($req, auth()->id());

            return redirect()->route("{$type}.request.index")->with('success', 'Pengajuan berhasil disetujui. Draf realisasi telah dibuat.');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', 'Gagal menyetujui pengajuan. Silakan coba lagi.');
        }
    }

    public function reject(RejectRequestRequest $request, $type, $id)
    {
        $req = RequestHeader::findOrFail($id);

        if ($req->status !== 'requested') {
            return redirect()->route("{$type}.request.index")->with('error', 'Hanya pengajuan berstatus Requested yang dapat ditolak.');
        }

        if ($req->created_by === auth()->id()) {
            return redirect()->route("{$type}.request.index")->with('error', 'Anda tidak dapat menolak pengajuan sendiri.');
        }

        try {
            $this->requestService->rejectRequest($req, auth()->id(), $request->rejection_reason);

            return redirect()->route("{$type}.request.index")->with('success', 'Pengajuan berhasil ditolak.');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', 'Gagal menolak pengajuan. Silakan coba lagi.');
        }
    }

    public function submit(Request $request, $type, $id)
    {
        $req = RequestHeader::findOrFail($id);

        if ($req->status !== 'draft') {
            return redirect()->route("{$type}.request.index")->with('error', 'Hanya pengajuan Draft yang dapat disubmit.');
        }

        try {
            $this->requestService->submitRequest($req);

            return redirect()->route("{$type}.request.index")->with('success', 'Pengajuan berhasil disubmit. Menunggu persetujuan Admin.');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', 'Gagal submit pengajuan. Silakan coba lagi.');
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
            report($e);
            return redirect()->back()->with('error', 'Gagal menghapus pengajuan.');
        }
    }
}
