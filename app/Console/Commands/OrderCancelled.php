<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Log;


class OrderCancelled extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:cancelled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Order cancelled of past date order which status are placed!';

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
        Log::info('================== Order cancelled crone job ================');
        try{
            $orders = Order::where('order_date','<',Carbon::now())->where('status',1)->get();
            foreach($orders as $order)
            {
                $order->status = 9;
                $order->save();
                $customer = User::find($order->order_by);
                sendPushNotification($customer->device_token, 'Order cancelled!','Your order '.$order->order_id.' has been cancelled, Please reschedule order.', 1, $customer->id, $order->id);
            }
        }catch(Exception $e){
            Log::info($e->getMessage());
        }
        
    }
}
