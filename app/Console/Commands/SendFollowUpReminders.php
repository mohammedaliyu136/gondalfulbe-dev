<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Extension\Models\FollowUpTask;

class SendFollowUpReminders extends Command
{
    protected $signature   = 'gondal:send-followup-reminders';
    protected $description = 'Log follow-up tasks due within 2 days so agents can be notified';

    public function handle(): int
    {
        $tasks = FollowUpTask::with('agent', 'visit')
            ->where('status', 'pending')
            ->whereBetween('due_date', [today(), today()->addDays(2)])
            ->get();

        foreach ($tasks as $task) {
            $this->info("Follow-up due: Visit #{$task->visit_id} | Agent: {$task->agent?->name} | Due: {$task->due_date->format('d/m/Y')} | Note: {$task->note}");
        }

        $this->info("Total follow-up tasks due within 2 days: {$tasks->count()}");

        return self::SUCCESS;
    }
}
