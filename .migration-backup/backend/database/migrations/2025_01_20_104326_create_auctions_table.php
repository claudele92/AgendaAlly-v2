<?php

use App\Models\AuctionQuestion;
use App\Models\AuctionUser;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAuctionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $supportsFullText = DB::connection()->getDriverName() !== 'sqlite';

        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->double('min_price')->index()->default(0);
            $table->unsignedBigInteger('brand_id')->index()->nullable();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('winner_id')->nullable()->index();
            $table->dateTime('start_at')->index();
            $table->dateTime('expired_at')->index();
            $table->string('status')->index()->nullable();
            $table->string('img')->nullable();
            $table->string('video')->nullable();
            $table->timestamp('created_at', 0)->index()->nullable();
            $table->timestamp('updated_at', 0)->index()->nullable();
        });

        Schema::create('auction_questions', function (Blueprint $table) use ($supportsFullText) {
            $table->id();
            $table->text('title')->nullable();
            if ($supportsFullText) {
                $table->fullText('title');
            }
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('auction_id')->index();
            $table->unsignedBigInteger('parent_id')->index()->nullable();
            $table->string('status')->index()->default(AuctionQuestion::STATUS_NEW);
            $table->timestamp('created_at', 0)->index()->nullable();
            $table->timestamp('updated_at', 0)->index()->nullable();
        });

        Schema::create('auction_translations', function (Blueprint $table) use ($supportsFullText) {
            $table->id();
            $table->unsignedBigInteger('auction_id')->index();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            if ($supportsFullText) {
                $table->fullText('title');
                $table->fullText('description');
            }
            $table->timestamp('created_at')->index()->nullable();
            $table->timestamp('updated_at')->index()->nullable();
        });

        Schema::create('auction_users', function (Blueprint $table) {
            $table->id();
            $table->double('price')->index()->nullable();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('auction_id')->index();
            $table->string('status')->default(AuctionUser::WAITING)->index();
            $table->timestamp('created_at')->index()->nullable();
            $table->timestamp('updated_at')->index()->nullable();
        });

        // general setting (key - auction_after_bid_time) - доп время после ставки если expired_at меньше 30 секунд
        // general setting (key - down_payment_price) - в % сколько нужно внести депозита для участия в аукционе. Если min price меньше либо равно 1 депозит будет составлять 1(default currency)
        // general setting (key - auction_time) - в минутах сколько продлиться аукцион
        // сумма последней ставки вывести в ресурсах
        // general setting (key - auction_refund_deposit) - делать ли возврат депозита тем кто не выиграл в аукционе (bool)
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('auction_questions');
        Schema::dropIfExists('auction_translations');
        Schema::dropIfExists('auction_users');
        Schema::dropIfExists('auctions');
    }
}
