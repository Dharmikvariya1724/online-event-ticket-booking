<?php

namespace App\Jobs;

use App\Models\VisitorLog;
use App\Services\VisitorGeoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EnrichVisitorLocation implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $visitorLogId,
        public string $ip
    ) {
    }

    public function handle(VisitorGeoService $geo): void
    {
        $log = VisitorLog::find($this->visitorLogId);
        if (!$log) {
            return;
        }

        if ($log->city || $log->region || $log->country) {
            return;
        }

        $data = $geo->lookup($this->ip);
        if (!$data) {
            return;
        }

        $log->fill($data);
        $log->save();
    }
}
