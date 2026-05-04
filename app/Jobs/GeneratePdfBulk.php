<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Job untuk generate slip gaji PDF secara massal via queue.
 *
 * Implementasi lengkap di Task 11.1.
 *
 * @see design.md — Application Layer: Jobs/Queue
 */
class GeneratePdfBulk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        // Implementasi lengkap di Task 11.1
    }

    public function handle(): void
    {
        // Implementasi lengkap di Task 11.1
    }
}
