<?php

namespace Totoro\Apollo\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use Illuminate\Console\Command;
use Totoro\Apollo\Commands\Helpers\Publisher;

class PublishComponentCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apollo:publish-component';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish apollo config';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Publish apollo config');

        (new Publisher($this))->publishComponent();

        $this->info('Publish finish');

    }


}
