@extends('layouts.master')

@section('content')

<div class="m-4 flex flex-wrap">
  @foreach ($chains as $chain)
    <div class="mb-4 mr-4">
      <h2 class="font-bold text-xl mb-2">{{ $chain->name }}</h2>

      <div class="mb-6 grid grid-cols-3 gap-0.5 lg:mb-8">
        @foreach ($chain->scannerRuns as $run)
          <div class="col-span-1 flex justify-center py-4 px-4 bg-{{ $run->statusColor }}-400{{ $run->status === 'STARTED' ? ' animated pulse infinite slow' : '' }}" title="{{ $run->status }}">
            <div class="text-center">
              <h3 class="text-xl font-semibold">{{ $run->created_at->timezone('America/New_York')->format('H:i') }}</h3>
              {{ $run->created_at->diffForHumans() }}
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endforeach
</div>

@endsection
