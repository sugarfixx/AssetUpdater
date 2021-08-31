<?php
/**
 * Created by PhpStorm.
 * User: sugarfixx
 * Date: 31/08/2021
 * Time: 21:33
 */

namespace App\Console\Commands;

use App\Queue;
use Illuminate\Console\Command;
use App\Library\AssetUpdater;

class RunAssetUpdater extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:assetUpdater';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'php artsian run:assetUpdater';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $assetUpdater = new AssetUpdater();
        $queue = Queue::where('done', 'false')->take(100)->get();

        $bar = $this->output->createProgressBar(count($queue));
        $bar->start();
        $time_start = microtime(true);
        foreach ($queue as $q) {
            $item = json_decode($q->item);
            $assetId = $item->assetId;
            $metadata = $item->metadata;
            $assetUpdater->updateAsset($assetId, $metadata);
            $q->done = true;
            $q->save();
            $bar->advance();
        }
        $bar->finish();
        $time_end = microtime(true);
        $this->info(' Asset Updater Completed in: ' . ($time_end - $time_start) . ' seconds');
    }
}
