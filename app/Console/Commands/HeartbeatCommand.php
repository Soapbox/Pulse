<?php

namespace App\Console\Commands;

use App\Heartbeat;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Helper\TableSeparator;

abstract class HeartbeatCommand extends Command
{
    /**
     * Render an error to the console
     *
     * @return void
     */
    public function renderError(string $string): void
    {
        $string = "  $string  ";
        $padding = str_repeat(' ', strlen($string));

        $this->error($padding);
        $this->error($string);
        $this->error($padding);
    }

    /**
     * Get the status to render for the given heartbeat
     *
     * @param \App\Heartbeat $heartbeat
     *
     * @return string
     */
    protected function getStatus(Heartbeat $heartbeat): string
    {
        if ($heartbeat->isMissing()) {
            return "<fg=red>{$heartbeat->status}</fg=red>";
        }
        return "<info>{$heartbeat->status}</info>";
    }

    /**
     * Convert the given heartbeat to an array for the row of the table to render
     *
     * @param \App\Heartbeat $heartbeat
     *
     * @return array
     */
    protected function renderHeartbeatRow(Heartbeat $heartbeat): array
    {
        return [
            $heartbeat->name,
            $heartbeat->getUrl(),
            $heartbeat->getWarnsAfter(),
            $heartbeat->getLeeway(),
            $heartbeat->last_check_in,
            $heartbeat->next_check_in,
            $this->getStatus($heartbeat),
        ];
    }

    /**
     * Render all of the given heartbeats in a table, grouped by status
     *
     * @param \Illuminate\Support\Collection $heartbeats
     *
     * @return void
     */
    protected function renderHeartbeats(Collection $heartbeats): void
    {
        $header = ['Name', 'URL', 'Warns After', 'Leeway', 'Last Check-In', 'Expected Check-In', 'Status'];

        $groups = $heartbeats->sortBy('name')->groupBy('status')->sortKeys();
        $rows = [];

        while ($groups->isNotEmpty()) {
            foreach ($groups->shift() as $heartbeat) {
                $rows[] = $this->renderHeartbeatRow($heartbeat);
            }

            if ($groups->isNotEmpty()) {
                $rows[] = new TableSeparator();
            }
        }

        $this->table($header, $rows);
    }

    /**
     * Render a single heartbeat in a table
     *
     * @param \App\Heartbeat $heartbeat
     *
     * @return void
     */
    protected function renderHeartbeat(Heartbeat $heartbeat): void
    {
        $this->renderHeartbeats(collect([$heartbeat]));
    }
}
