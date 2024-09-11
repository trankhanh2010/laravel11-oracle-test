<?php

namespace App\Providers;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AccidentBodyPart\CreateAccidentBodyPartIndex;
use App\Events\Elastic\AccidentBodyPart\InsertAccidentBodyPartIndex;
use App\Events\Elastic\AccidentCare\CreateAccidentCareIndex;
use App\Events\Elastic\AccidentCare\InsertAccidentCareIndex;
use App\Events\Elastic\AccidentHurtType\CreateAccidentHurtTypeIndex;
use App\Events\Elastic\AccidentHurtType\InsertAccidentHurtTypeIndex;
use App\Events\Elastic\AccidentLocation\CreateAccidentLocationIndex;
use App\Events\Elastic\AccidentLocation\InsertAccidentLocationIndex;
use App\Events\Elastic\AgeType\CreateAgeTypeIndex;
use App\Events\Elastic\Area\CreateAreaIndex;
use App\Events\Elastic\Area\InsertAreaIndex;
use App\Events\Elastic\AtcGroup\CreateAtcGroupIndex;
use App\Events\Elastic\AtcGroup\InsertAtcGroupIndex;
use App\Events\Elastic\Awareness\CreateAwarenessIndex;
use App\Events\Elastic\Awareness\InsertAwarenessIndex;
use App\Events\Elastic\Bed\CreateBedIndex;
use App\Events\Elastic\Bed\InsertBedIndex;
use App\Events\Elastic\BedBsty\CreateBedBstyIndex;
use App\Events\Elastic\BedBsty\InsertBedBstyIndex;
use App\Events\Elastic\BedRoom\CreateBedRoomIndex;
use App\Events\Elastic\BedRoom\InsertBedRoomIndex;
use App\Events\Elastic\BedType\CreateBedTypeIndex;
use App\Events\Elastic\BhytBlacklist\CreateBhytBlacklistIndex;
use App\Events\Elastic\BhytBlacklist\InsertBhytBlacklistIndex;
use App\Events\Elastic\BhytParam\CreateBhytParamIndex;
use App\Events\Elastic\BhytParam\InsertBhytParamIndex;
use App\Events\Elastic\BhytWhitelist\CreateBhytWhitelistIndex;
use App\Events\Elastic\BhytWhitelist\InsertBhytWhitelistIndex;
use App\Events\Elastic\BidType\CreateBidTypeIndex;
use App\Events\Elastic\BidType\InsertBidTypeIndex;
use App\Events\Elastic\BloodGroup\CreateBloodGroupIndex;
use App\Events\Elastic\BloodGroup\InsertBloodGroupIndex;
use App\Events\Elastic\BloodVolume\CreateBloodVolumeIndex;
use App\Events\Elastic\BloodVolume\InsertBloodVolumeIndex;
use App\Events\Elastic\BodyPart\CreateBodyPartIndex;
use App\Events\Elastic\BodyPart\InsertBodyPartIndex;
use App\Events\Elastic\BornPosition\CreateBornPositionIndex;
use App\Events\Elastic\BornPosition\InsertBornPositionIndex;
use App\Events\Elastic\Branch\CreateBranchIndex;
use App\Events\Elastic\Branch\InsertBranchIndex;
use App\Events\Elastic\CancelReason\CreateCancelReasonIndex;
use App\Events\Elastic\CancelReason\InsertCancelReasonIndex;
use App\Events\Elastic\DeleteIndex;
use App\Events\Telegram\SendMessageToChannel;
use App\Listeners\Cache\DeleteCache as CacheDeleteCache;
use App\Listeners\Elastic\AccidentBodyPart\ElasticCreateAccidentBodyPartIndex;
use App\Listeners\Elastic\AccidentBodyPart\ElasticInsertAccidentBodyPartIndex;
use App\Listeners\Elastic\AccidentCare\ElasticCreateAccidentCareIndex;
use App\Listeners\Elastic\AccidentCare\ElasticInsertAccidentCareIndex;
use App\Listeners\Elastic\AccidentHurtType\ElasticCreateAccidentHurtTypeIndex;
use App\Listeners\Elastic\AccidentHurtType\ElasticInsertAccidentHurtTypeIndex;
use App\Listeners\Elastic\AccidentLocation\ElasticCreateAccidentLocationIndex;
use App\Listeners\Elastic\AccidentLocation\ElasticInsertAccidentLocationIndex;
use App\Listeners\Elastic\AgeType\ElasticCreateAgeTypeIndex;
use App\Listeners\Elastic\Area\ElasticCreateAreaIndex;
use App\Listeners\Elastic\Area\ElasticInsertAreaIndex;
use App\Listeners\Elastic\AtcGroup\ElasticCreateAtcGroupIndex;
use App\Listeners\Elastic\AtcGroup\ElasticInsertAtcGroupIndex;
use App\Listeners\Elastic\Awareness\ElasticCreateAwarenessIndex;
use App\Listeners\Elastic\Awareness\ElasticInsertAwarenessIndex;
use App\Listeners\Elastic\Bed\ElasticCreateBedIndex;
use App\Listeners\Elastic\Bed\ElasticInsertBedIndex;
use App\Listeners\Elastic\BedBsty\ElasticCreateBedBstyIndex;
use App\Listeners\Elastic\BedBsty\ElasticInsertBedBstyIndex;
use App\Listeners\Elastic\BedRoom\ElasticCreateBedRoomIndex;
use App\Listeners\Elastic\BedRoom\ElasticInsertBedRoomIndex;
use App\Listeners\Elastic\BedType\ElasticCreateBedTypeIndex;
use App\Listeners\Elastic\BhytBlacklist\ElasticCreateBhytBlacklistIndex;
use App\Listeners\Elastic\BhytBlacklist\ElasticInsertBhytBlacklistIndex;
use App\Listeners\Elastic\BhytParam\ElasticCreateBhytParamIndex;
use App\Listeners\Elastic\BhytParam\ElasticInsertBhytParamIndex;
use App\Listeners\Elastic\BhytWhitelist\ElasticCreateBhytWhitelistIndex;
use App\Listeners\Elastic\BhytWhitelist\ElasticInsertBhytWhitelistIndex;
use App\Listeners\Elastic\BidType\ElasticCreateBidTypeIndex;
use App\Listeners\Elastic\BidType\ElasticInsertBidTypeIndex;
use App\Listeners\Elastic\BloodGroup\ElasticCreateBloodGroupIndex;
use App\Listeners\Elastic\BloodGroup\ElasticInsertBloodGroupIndex;
use App\Listeners\Elastic\BloodVolume\ElasticCreateBloodVolumeIndex;
use App\Listeners\Elastic\BloodVolume\ElasticInsertBloodVolumeIndex;
use App\Listeners\Elastic\BodyPart\ElasticCreateBodyPartIndex;
use App\Listeners\Elastic\BodyPart\ElasticInsertBodyPartIndex;
use App\Listeners\Elastic\BornPosition\ElasticCreateBornPositionIndex;
use App\Listeners\Elastic\BornPosition\ElasticInsertBornPositionIndex;
use App\Listeners\Elastic\Branch\ElasticCreateBranchIndex;
use App\Listeners\Elastic\Branch\ElasticInsertBranchIndex;
use App\Listeners\Elastic\CancelReason\ElasticCreateCancelReasonIndex;
use App\Listeners\Elastic\CancelReason\ElasticInsertCancelReasonIndex;
use App\Listeners\Elastic\ElasticDeleteIndex;
use App\Listeners\Telegram\TelegramSendMessageToChannel;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // Cache
        DeleteCache::class => [
            CacheDeleteCache::class,
        ],

        // Telegram
        SendMessageToChannel::class => [
            TelegramSendMessageToChannel::class,
        ],

        // Elastic Search
        DeleteIndex::class => [
            ElasticDeleteIndex::class,
        ],

        CreateAccidentBodyPartIndex::class => [
            ElasticCreateAccidentBodyPartIndex::class,
        ],
        InsertAccidentBodyPartIndex::class => [
            ElasticInsertAccidentBodyPartIndex::class,
        ],

        CreateAccidentCareIndex::class => [
            ElasticCreateAccidentCareIndex::class,
        ],
        InsertAccidentCareIndex::class => [
            ElasticInsertAccidentCareIndex::class,
        ],

        CreateAccidentHurtTypeIndex::class => [
            ElasticCreateAccidentHurtTypeIndex::class,
        ],
        InsertAccidentHurtTypeIndex::class => [
            ElasticInsertAccidentHurtTypeIndex::class,
        ],

        CreateAccidentLocationIndex::class => [
            ElasticCreateAccidentLocationIndex::class,
        ],
        InsertAccidentLocationIndex::class => [
            ElasticInsertAccidentLocationIndex::class,
        ],

        CreateAgeTypeIndex::class => [
            ElasticCreateAgeTypeIndex::class,
        ],
        
        CreateAreaIndex::class => [
            ElasticCreateAreaIndex::class,
        ],
        InsertAreaIndex::class => [
            ElasticInsertAreaIndex::class,
        ],

        CreateAtcGroupIndex::class => [
            ElasticCreateAtcGroupIndex::class,
        ],
        InsertAtcGroupIndex::class => [
            ElasticInsertAtcGroupIndex::class,
        ],

        CreateAwarenessIndex::class => [
            ElasticCreateAwarenessIndex::class,
        ],
        InsertAwarenessIndex::class => [
            ElasticInsertAwarenessIndex::class,
        ],

        CreateBedBstyIndex::class => [
            ElasticCreateBedBstyIndex::class,
        ],
        InsertBedBstyIndex::class => [
            ElasticInsertBedBstyIndex::class,
        ],

        CreateBedIndex::class => [
            ElasticCreateBedIndex::class,
        ],
        InsertBedIndex::class => [
            ElasticInsertBedIndex::class,
        ],

        CreateBedRoomIndex::class => [
            ElasticCreateBedRoomIndex::class,
        ],
        InsertBedRoomIndex::class => [
            ElasticInsertBedRoomIndex::class,
        ],

        CreateBedTypeIndex::class => [
            ElasticCreateBedTypeIndex::class,
        ],

        CreateBhytBlacklistIndex::class => [
            ElasticCreateBhytBlacklistIndex::class,
        ],
        InsertBhytBlacklistIndex::class => [
            ElasticInsertBhytBlacklistIndex::class,
        ],

        CreateBhytParamIndex::class => [
            ElasticCreateBhytParamIndex::class,
        ],
        InsertBhytParamIndex::class => [
            ElasticInsertBhytParamIndex::class,
        ],

        CreateBhytWhitelistIndex::class => [
            ElasticCreateBhytWhitelistIndex::class,
        ],
        InsertBhytWhitelistIndex::class => [
            ElasticInsertBhytWhitelistIndex::class,
        ],

        CreateBidTypeIndex::class => [
            ElasticCreateBidTypeIndex::class,
        ],
        InsertBidTypeIndex::class => [
            ElasticInsertBidTypeIndex::class,
        ],

        CreateBloodGroupIndex::class => [
            ElasticCreateBloodGroupIndex::class,
        ],
        InsertBloodGroupIndex::class => [
            ElasticInsertBloodGroupIndex::class,
        ],

        CreateBloodVolumeIndex::class => [
            ElasticCreateBloodVolumeIndex::class,
        ],
        InsertBloodVolumeIndex::class => [
            ElasticInsertBloodVolumeIndex::class,
        ],

        CreateBodyPartIndex::class => [
            ElasticCreateBodyPartIndex::class,
        ],
        InsertBodyPartIndex::class => [
            ElasticInsertBodyPartIndex::class,
        ],

        CreateBornPositionIndex::class => [
            ElasticCreateBornPositionIndex::class,
        ],
        InsertBornPositionIndex::class => [
            ElasticInsertBornPositionIndex::class,
        ],

        CreateBranchIndex::class => [
            ElasticCreateBranchIndex::class,
        ],
        InsertBranchIndex::class => [
            ElasticInsertBranchIndex::class,
        ],

        CreateCancelReasonIndex::class => [
            ElasticCreateCancelReasonIndex::class,
        ],
        InsertCancelReasonIndex::class => [
            ElasticInsertCancelReasonIndex::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
