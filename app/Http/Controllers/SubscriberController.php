<?php

namespace App\Http\Controllers;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Rules\PhoneNumber;
use App\Subscriber;
use App\Chain;

class SubscriberController extends Controller
{
    public function __invoke(Request $request) {
        $validChains = Chain::pluck('name');

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
        $subscriber->radius = $request->input('radius');
        $subscriber->criteria = $request->input('criteria');
        $subscriber->save();

        // TODO

        $chains = $request->input('chains');

        return response()->json([
            'count' => 5
        ]);
    }

    private function getFormattedPhone($inputPhone) {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $proto = $phoneUtil->parse($inputPhone, 'US');
        $phone = $phoneUtil->format($proto, PhoneNumberFormat::E164);

        return $phone;
    }
}
