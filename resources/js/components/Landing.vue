<template>
<div>
  <div class="relative bg-white overflow-hidden">
    <div class="max-w-screen-xl mx-auto">
      <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
        <div class="pt-6 px-4 sm:px-6 lg:px-8">
          <nav class="relative flex items-center justify-between sm:h-10 lg:justify-start">
            <a href="/">
              <img class="h-8 w-auto sm:h-10" src="/img/logo.svg" alt="Curbside" />
            </a>
          </nav>
        </div>
        <div class="mt-10 mx-auto max-w-screen-xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
          <div class="sm:text-center lg:text-left">
            <h2 class="text-4xl tracking-tight leading-10 font-extrabold text-gray-900 sm:text-5xl sm:leading-none md:text-6xl">
              Get your grocery
              <br class="xl:hidden" />
              <span class="text-orange-600">curbside pickup</span>
              slot
            </h2>
            <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
              Pickup slots are scarce right now. Stop refreshing your browser and get notified when a slot opens up instead.
            </p>
            <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
              <div class="rounded-md shadow">
                <button @click.prevent="launch" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base leading-6 font-medium rounded-md text-white bg-orange-600 hover:bg-orange-500 focus:outline-none focus:shadow-outline transition duration-150 ease-in-out md:py-4 md:text-lg md:px-10">
                  Get started
                </button>
              </div>
              <div class="mt-3 sm:mt-0 sm:ml-3">
                <a href="/about" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base leading-6 font-medium rounded-md text-orange-700 bg-orange-100 hover:text-orange-600 hover:bg-orange-50 focus:outline-none focus:shadow-outline focus:border-orange-300 transition duration-150 ease-in-out md:py-4 md:text-lg md:px-10">
                  About
                </a>
              </div>
            </div>
          </div>
        </div>
        <svg class="hidden lg:block absolute right-0 inset-y-0 h-full w-48 text-white transform translate-x-1/2" fill="currentColor" viewBox="0 0 100 100" preserveAspectRatio="none">
          <polygon points="50,0 100,0 50,100 0,100" />
        </svg>
      </div>
    </div>
    <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2">
      <img class="h-56 w-full object-cover sm:h-72 md:h-96 lg:w-full lg:h-full" src="https://images.unsplash.com/photo-1543168256-418811576931?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=2850&q=80" alt="" />
    </div>
  </div>

  <location-modal v-if="loading || errorMessage" :errorMessage="errorMessage" @retry="launch"></location-modal>
  <notification-subscription-modal v-if="location" :location="location"></notification-subscription-modal>
</div>
</template>
<script>
  export default {
    data() {
      return {
        location: null,
        loading: false,
        errorMessage: null
      };
    },
    methods: {
      launch() {
        if (navigator.geolocation) {
          this.loading = true;

          navigator.geolocation.getCurrentPosition(location => {
            this.loading = false;
            this.location = [location.coords.latitude, location.coords.longitude];
          }, err => {
            this.loading = false;
            this.handleError(err);
          });
        } else {
          this.errorMessage = 'Your browser does not appear to support geolocation. Please try another browser.';
        }
      },
      handleError(err) {
        switch (err.code) {
          case 1:
            this.errorMessage = 'Please give us permission to get your location.';
            break;

          case 2:
            this.errorMessage = 'Could not get your location. Please try again later or use a different browser.';
            break;

          case 3:
            this.errorMessage = 'It took to longer to find your location. Please try again later or use a different browser.';
            break;

          default:
            this.errorMessage = err.message;
            break;
        }
      }
    }
  }
</script>
