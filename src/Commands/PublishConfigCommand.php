<?php

namespace Totoro\Apollo\Commands;

use Illuminate\Console\Command;
use Totoro\Apollo\Commands\Helpers\Publisher;

class PublishConfigCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apollo:publish-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish config';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Publish config files');

        (new Publisher($this))->publishFile(
            __DIR__ . '/../config/',
            base_path('config'),
            'apollo.php'
        );
    }
}
