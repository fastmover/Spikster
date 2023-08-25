<?php

namespace App\Jobs;

use phpseclib3\Net\SSH2;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class NewSiteSSH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $server;
    protected $site;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($server, $site)
    {
        $this->server   = $server;
        $this->site     = $site;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ssh = new SSH2($this->server->ip, 22);
        $ssh->login('cipi', $this->server->password);
        $ssh->setTimeout(360);
        $ssh->exec('echo '.$this->server->password.' | sudo -S sudo unlink newsite');
        $ssh->exec('echo '.$this->server->password.' | sudo -S sudo wget '.config('app.url').'/sh/newsite');

        if ($this->site->language == "NODEJS") {
            $ssh->exec('echo '.$this->server->password.' | sudo -S sudo wget -O newsite  '.config('app.url').'/sh/newsite_nodejs');
        } else {
            $ssh->exec('echo '.$this->server->password.' | sudo -S sudo wget '.config('app.url').'/sh/newsite');
        }
    }
}
