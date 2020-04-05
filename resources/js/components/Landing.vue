<template>
<div>
  <div class="relative bg-white overflow-hidden">
    <div class="max-w-screen-xl mx-auto">
      <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
        <div class="pt-6 px-4 sm:px-6 lg:px-8">
          <nav class="relative flex items-center justify-between sm:h-10 lg:justify-start">
            <a href="/">
              <svg class="h-8 w-auto sm:h-10" fill="#d03801" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M37.5 50h10.02a2.5 2.5 0 0 0 1.57-4.446l-4.822-4.821A2.501 2.501 0 0 0 42.5 40h-5a2.5 2.5 0 0 0-2.5 2.5v5a2.5 2.5 0 0 0 2.5 2.5zM22.5 50H30a2.5 2.5 0 0 0 2.5-2.5v-5A2.5 2.5 0 0 0 30 40h-2.5c-.663 0-1.299.264-1.768.732l-5 5A2.5 2.5 0 0 0 22.5 50z"/><path d="M27.063 65h20.875c1.033 2.908 3.804 5 7.063 5s6.03-2.092 7.063-5H67.5a2.5 2.5 0 0 0 2.5-2.5v-15a2.5 2.5 0 0 0-2.5-2.5h-8.965L46.768 33.232A2.504 2.504 0 0 0 45 32.5H25c-.663 0-1.299.264-1.768.732l-9.583 9.582-6.94 2.313A2.503 2.503 0 0 0 5 47.5v15A2.5 2.5 0 0 0 7.5 65h5.438c1.033 2.908 3.804 5 7.063 5s6.029-2.092 7.062-5zM55 65c-1.379 0-2.5-1.121-2.5-2.5S53.621 60 55 60s2.5 1.121 2.5 2.5S56.379 65 55 65zm-42.062-5H10V49.302l5.791-1.93c.368-.123.702-.33.977-.604l9.268-9.268h17.93l11.768 11.768A2.496 2.496 0 0 0 57.5 50H65v10h-2.938c-1.033-2.908-3.804-5-7.063-5s-6.03 2.092-7.063 5H27.063c-1.033-2.908-3.804-5-7.063-5s-6.03 2.092-7.062 5zM20 65c-1.379 0-2.5-1.121-2.5-2.5S18.621 60 20 60s2.5 1.121 2.5 2.5S21.379 65 20 65z"/><path d="M92.5 65h-15a2.5 2.5 0 0 0-2.5 2.5V70H7.5c-3.224 0-3.224 5 0 5h70a2.5 2.5 0 0 0 2.5-2.5V70h12.5c3.224 0 3.224-5 0-5z"/></svg>
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
  <notification-subscription-modal v-if="location" :location="location" @dismiss="handleDismiss"></notification-subscription-modal>
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
      handleDismiss() {
        this.location = null;
        this.loading = false;
        this.errorMessage = null;
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
