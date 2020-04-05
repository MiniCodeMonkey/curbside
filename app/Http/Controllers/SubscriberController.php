<?php

namespace App\Http\Controllers;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Notifications\SubscriptionCreated;
use App\Rules\PhoneNumber;
use App\Subscriber;
use App\Chain;
use App\Store;

class SubscriberController extends Controller
{
    public function __invoke(Request $request) {
        $request->validate([
            'radius' => ['required', 'integer', 'gte:1', 'lt:300'],
            'chains' => ['required', 'array', Rule::in(Chain::pluck('name'))],
            'phone' => ['required', new PhoneNumber],
            'criteria' => ['required', Rule::in(['ANYTIME', 'SOON', 'TODAY'])],
            'location' => ['required', 'array', 'size:2']
        ]);

        $phone = $this->getFormattedPhone($request->input('phone'));
        list($lat, $lng) = $request->input('location');

        $subscriber = Subscriber::firstOrNew(['phone' => $phone]);
        $subscriber->location = new Point($lat, $lng);
        $subscriber->phone = $phone;
        $subscriber->radius = intval($request->input('radius'));
        $subscriber->criteria = $request->input('criteria');
        $subscriber->status = 'ACTIVE';
        $subscriber->save();

        $stores = $this->updateSubscriptions($subscriber, $request->input('chains'));

        $subscriber->notify(new SubscriptionCreated($stores->count()));

        return response()->json([
            'count' => $stores->count()
        ]);
    }

    private function updateSubscriptions(Subscriber $subscriber, array $chainNames) {
        $chainIds = Chain::whereIn('name', $chainNames)->pluck('id');
        $matchedStores = Store::whereIn('chain_id', $chainIds)
            ->distanceSphere('location', $subscriber->location, $subscriber->radiusInMeters())
            ->pluck('id');

        if ($matchedStores->count() <= 0) {
            $distance = $subscriber->radius == 1
                ? $subscriber->radius . ' mile'
                : $subscriber->radius . ' miles';

            throw ValidationException::withMessages(['radius' => 'Did not find any stores within ' . $distance . '.']);
        }

        $subscriber->stores()->detach();
        $subscriber->stores()->attach($matchedStores);

        return $matchedStores;
    }

    private function getFormattedPhone($inputPhone) {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $proto = $phoneUtil->parse($inputPhone, 'US');
        $phone = $phoneUtil->format($proto, PhoneNumberFormat::E164);

        return $phone;
    }
}
