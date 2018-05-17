<?php

namespace Totoro\Apollo\Commands;

use Illuminate\Console\Command;

class ClearApolloCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apollo:clear-apollo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear apollo all config';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Clear apollo all config');
        app('apollo')->clear();
        $this->info('Clear finish');

    }
}
