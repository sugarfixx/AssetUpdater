<?php
/**
 * Created by PhpStorm.
 * User: sugarfixx
 * Date: 31/08/2021
 * Time: 21:33
 */

namespace App\Console\Commands;

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

    }
}
