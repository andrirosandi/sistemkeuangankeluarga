<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait BulkDeletable
{
    public function bulkDelete(Request $request)
    {
        $config = $this->bulkDeleteConfig();

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:' . $config['table'] . ',id',
        ]);

        if (method_exists($this, 'beforeBulkDelete')) {
            $error = $this->beforeBulkDelete($request);
            if ($error) {
                return $error;
            }
        }

        try {
            if (isset($config['use_transaction']) && $config['use_transaction']) {
                DB::beginTransaction();
            }

            $config['model']::whereIn('id', $request->ids)->delete();

            if (isset($config['use_transaction']) && $config['use_transaction']) {
                DB::commit();
            }

            return redirect()->back()->with('success', count($request->ids) . ' ' . $config['label'] . ' berhasil dihapus.');
        } catch (\Exception $e) {
            if (isset($config['use_transaction']) && $config['use_transaction']) {
                DB::rollBack();
            }
            return redirect()->back()->with('error', 'Gagal menghapus ' . $config['label'] . '! Beberapa data mungkin sudah digunakan.');
        }
    }

    /**
     * Define bulk delete configuration.
     * Override in controller: return ['model' => ModelClass::class, 'table' => 'table_name', 'label' => 'label'];
     */
    abstract protected function bulkDeleteConfig(): array;
}
