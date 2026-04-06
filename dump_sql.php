<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = new \App\Models\User(['id' => 2]); // Simulate Istri

// Since we cannot run DB queries to get roles, we just mock visibleUserIds
$visibleUserIds = collect([2]);

$query = \App\Models\RequestHeader::where('trans_code', 2)
    ->where(function ($q) use ($visibleUserIds, $user) {
        $q->where('created_by', $user->id)
          ->orWhere(function ($q2) use ($visibleUserIds) {
              $q2->whereIn('created_by', $visibleUserIds)
                 ->where('status', '!=', 'draft');
          });
    });

echo "REQUEST QUERY:\n";
echo $query->toSql() . "\n\n";

$query2 = \App\Models\TransactionHeader::where('trans_code', 2)
    ->where(function ($q) use ($visibleUserIds, $user) {
        $q->where('created_by', $user->id)
          ->orWhereIn('created_by', $visibleUserIds)
          ->orWhereHas('requestHeader', function ($rq) use ($visibleUserIds, $user) {
              $rq->where('created_by', $user->id)
                 ->orWhereIn('created_by', $visibleUserIds);
          });
    });

echo "TRANSACTION QUERY:\n";
echo $query2->toSql() . "\n";
