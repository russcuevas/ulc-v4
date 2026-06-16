<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SendLoanStartSms extends Command
{
    protected $signature = 'sms:loan-start';
    protected $description = 'Send SMS to clients whose loan starts today';

    public function handle(): int
    {
        $today = Carbon::today()->toDateString();

        $loans = DB::table('clients_loans')
            ->join('clients', 'clients_loans.client_id', '=', 'clients.id')
            ->where('clients_loans.loan_from', $today)
            ->where('clients_loans.loan_start_sms_sent', false)
            ->whereNotNull('clients.phone')
            ->where('clients.phone', '!=', '')
            ->select(
                'clients_loans.id as loan_id',
                'clients.fullname',
                'clients.phone',
                'clients_loans.loan_from'
            )
            ->get();

        $sent = 0;
        $messages = [];

        foreach ($loans as $loan) {
            $loanDate = Carbon::parse($loan->loan_from)->format('Y-m-d');
            $messages[] = [
                'number' => $loan->phone,
                'message' => "Magandang araw {$loan->fullname}! Ngayong araw ay simula ng iyong loan ({$loanDate}). Makakatanggap ka ng araw-araw na paalala na payment. Salamat po!\n- Ultraritz Lending Corporation"
            ];
        }

        if (count($messages) > 0) {
            try {
                \sendMessages($messages);

                DB::table('clients_loans')
                    ->whereIn('id', $loans->pluck('loan_id'))
                    ->update(['loan_start_sms_sent' => true]);

                $sent = count($messages);
                foreach ($loans as $loan) {
                    $this->info("SMS sent to {$loan->fullname} ({$loan->phone})");
                }
            } catch (\Exception $e) {
                $this->error("Failed to send bulk SMS: {$e->getMessage()}");
            }
        }

        $this->info("Done. Sent {$sent} SMS out of {$loans->count()} loans starting today.");


        return self::SUCCESS;
    }
}
