<?php
declare(strict_types=1);
namespace App\Services\PushNotificationService;
use App\Models\{Booking,PushNotification};
use Illuminate\Support\Facades\DB;

final class SelectedBookingNotifications
{
    /** Retain inside the booking transaction, never an afterResponse closure. */
    public function store(Booking $booking,array $data,array $recipients,?string $title,?string $body): void
    {
        DB::transaction(function()use($booking,$data,$recipients,$title,$body):void{
            $locked=Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $type=(string)($data['type']??PushNotification::NEW_BOOKING);
            $identity=$type===PushNotification::BOOKING_NOTIFICATION
                ?(string)$locked->start_date
                :(string)$locked->updated_at.'|'.json_encode($data,JSON_THROW_ON_ERROR);
            $key=hash('sha256',$locked->id.'|'.$type.'|'.$identity);
            foreach(array_unique($recipients) as $recipient) {
                if (!$recipient) continue;
                $exists=PushNotification::where('model_id',$locked->id)->where('model_type',$locked->getMorphClass())
                    ->where('user_id',$recipient)->where('data->_mvp_event_key',$key)->exists();
                if (!$exists) PushNotification::create([
                    'model_id'=>$locked->id,'model_type'=>$locked->getMorphClass(),'user_id'=>$recipient,
                    'type'=>$type,'title'=>$title,'body'=>$body,'data'=>$data+['_mvp_event_key'=>$key],
                ]);
            }
        },3);
    }
}