<div class="bg-teal-800">
  <div class="max-w-screen-xl mx-auto py-12 px-4 sm:py-16 sm:px-6 lg:px-8 lg:py-20">
    <div class="max-w-4xl mx-auto text-center">
      <h2 class="text-3xl leading-9 font-extrabold text-white sm:text-4xl sm:leading-10">
        So far, so good
      </h2>
    </div>
    <div class="mt-10 text-center sm:max-w-3xl sm:mx-auto sm:grid sm:grid-cols-4 sm:gap-5">
      <div>
        <p class="text-5xl leading-none font-extrabold text-white">
          {{ number_format(App\Store::count()) }}
        </p>
        <p class="mt-2 text-lg leading-6 font-medium text-teal-200">
          stores
        </p>
      </div>
      <div class="mt-10 sm:mt-0">
        <p class="text-5xl leading-none font-extrabold text-white">
          {{ number_format(App\Timeslot::count()) }}
        </p>
        <p class="mt-2 text-lg leading-6 font-medium text-teal-200">
          timeslots found
        </p>
      </div>
      <div class="mt-10 sm:mt-0">
        <p class="text-5xl leading-none font-extrabold text-white">
          {{ number_format(App\Subscriber::active()->count()) }}
        </p>
        <p class="mt-2 text-lg leading-6 font-medium text-teal-200">
          active subscriptions
        </p>
      </div>
      <div class="mt-10 sm:mt-0">
        <p class="text-5xl leading-none font-extrabold text-white">
          {{ App\Timeslot::latestCreatedAt(true) }}
        </p>
        <p class="mt-2 text-lg leading-6 font-medium text-teal-200">
          latest timeslot found
        </p>
      </div>
    </div>
  </div>
</div>
