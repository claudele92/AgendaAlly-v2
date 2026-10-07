<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Log;
use Throwable;
use App\Models\Auction;
use App\Models\Settings;
use App\Models\AuctionUser;
use App\Traits\PaymentRefund;
use Illuminate\Console\Command;

class AuctionUpdate extends Command
{
    use PaymentRefund;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auction:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auction update';

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
    public function handle(): int
    {
        sleep(1);

        /** @var Auction[] $auctions */
        $auctions = Auction::with(['users' => fn($q) => $q->whereIn('status', AuctionUser::ACTIVE)])
            ->where('expired_at', '<=', now())
            ->get();

        $auctionRefundDeposit = Settings::where('key', 'auction_refund_deposit')->first()?->value;

        foreach ($auctions as $auction) {

            /** @var AuctionUser $auctionUser */
            $auctionUser = $auction->users
                ->sortByDesc('price')
                ->first();

            $auction->update([
                'status'    => Auction::ENDED,
                'winner_id' => $auctionUser->user_id
            ]);

            if ($auctionRefundDeposit) {

                foreach ($auction->users as $auctionUser) {

                    try {
                        $this->paymentRefund($auctionUser);
                    } catch (Throwable $e) {
                        Log::error($e->getMessage() . $e->getFile() . $e->getLine());
                    }

                }

            }

        }

        return 0;
    }
}
