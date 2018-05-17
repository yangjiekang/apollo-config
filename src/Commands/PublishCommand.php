<?php

namespace Totoro\Apollo\Commands;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apollo:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish config, views, assets';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Publishing all files');
        $this->call('apollo:publish-config');
    }
}
