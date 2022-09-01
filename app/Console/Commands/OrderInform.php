<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Log;

class OrderInform extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:inform';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Before 24 hour inform to admin placed order';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('================== Order Inform to admin crone ================');
        try{
            $nextDay = Carbon::today()->addDays(1);
            $orders = Order::where('order_date',$nextDay)->where('status',1)->get();
            foreach($orders as $order)
            {
                $admin = User::find(1);
                sendPushNotification($admin->device_token, 'You have order!','You have order '.$order->order_id.'.Please accept order. ', 1, $admin->id, $order->id);
            }
        }catch(Exception $e){
            Log::info($e->getMessage());
        }
    }
}
