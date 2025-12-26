<?php

namespace App\Console\Commands;

use App\Jobs\SendDailySalesReport;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class SendDailySalesReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:report-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily sales report to admin email';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        SendDailySalesReport::dispatch();
        return CommandAlias::SUCCESS;
    }
}
