<template>
  <div class="fixed bottom-0 inset-x-0 z-1000 px-4 pb-6 sm:inset-0 sm:p-0 sm:flex sm:items-center sm:justify-center">
    <transition
        appear
        enter-active-class="transition-opacity ease-out duration-300" enter-class="transition-opacity opacity-0" enter-to-class="transition-opacity opacity-100"
        leave-active-class="transition-opacity ease-in duration-200" leave-class="transition-opacity opacity-100" leave-to-class="transition-opacity opacity-0"
    >
        <div class="fixed inset-0">
          <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
    </transition>

    <transition
        appear
        enter-active-class="transition-all ease-out duration-300" enter-class="transition-all opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to-class="transition-all opacity-100 translate-y-0 sm:scale-100"
        leave-active-class="transition-all ease-in duration-200" leave-class="transition-all opacity-100 translate-y-0 sm:scale-100" leave-to-class="transition-all opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        <form @submit.prevent="search" class="bg-white rounded-lg px-4 pt-5 pb-4 overflow-hidden shadow-xl transform sm:max-w-sm sm:w-full sm:p-6">
          <div>
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
              <svg class="h-6 w-6 text-green-600 infinite heartBeat slower" :class="loading ? 'animated' : ''" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path d="M17.6569 16.6569C16.7202 17.5935 14.7616 19.5521 13.4138 20.8999C12.6327 21.681 11.3677 21.6814 10.5866 20.9003C9.26234 19.576 7.34159 17.6553 6.34315 16.6569C3.21895 13.5327 3.21895 8.46734 6.34315 5.34315C9.46734 2.21895 14.5327 2.21895 17.6569 5.34315C20.781 8.46734 20.781 13.5327 17.6569 16.6569Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M15 11C15 12.6569 13.6569 14 12 14C10.3431 14 9 12.6569 9 11C9 9.34315 10.3431 8 12 8C13.6569 8 15 9.34315 15 11Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>

            <div class="mt-3 text-center sm:mt-5">
              <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2">
                {{geolocationFailed ? 'Could not get your location automagically': 'Locating...'}}
              </h3>
              <div v-if="geolocationFailed">
                <label for="zip" class="block text-sm font-medium leading-5 text-gray-700">Enter your US zip code</label>

                <input id="zip" v-model="zip" class="form-input rounded-md shadow-sm block w-20 sm:text-sm sm:leading-5 mt-1 mx-auto" pattern="\d*" maxlength="5" required />
              </div>
              <p v-else class="text-sm leading-5 text-gray-500">
                We need your location to find grocery stores near you. It will not be used for any other purpose.
              </p>

              <p v-if="errorMessage" class="mt-2 text-sm text-red-500">
                {{ errorMessage }}
              </p>
            </div>
          </div>
          <div class="mt-5 sm:mt-6" v-if="geolocationFailed">
            <span class="flex w-full rounded-md shadow-sm">
              <button type="submit" :disabled="loading" class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-blue-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                {{ loading ? 'Loading' : 'Next' }}
              </button>
            </span>
          </div>
        </form>
    </transition>
  </div>
</template>
<script>
  export default {
    created() {
      if (navigator.geolocation) {
        this.loading = true;

        navigator.geolocation.getCurrentPosition(location => {
          this.loading = false;
          this.$emit('locationFound', [location.coords.latitude, location.coords.longitude]);
        }, err => {
          this.loading = false;
          this.geolocationFailed = true;
        });
      } else {
        this.geolocationFailed = true;
      }
    },
    data() {
      return {
        zip: '',
        geolocationFailed: false,
        errorMessage: null,
        loading: false
      };
    },
    methods: {
      async search() {
        if (this.zip.length !== 5) {
          this.errorMessage = 'Please enter a 5-digit zip code';
        } else {
          this.errorMessage = null;

          this.loading = true;

          try {
            const query = encodeURIComponent(this.zip);
            const response = await fetch(`https://api.geocodio.dev/v1.4/geocode?api_key=DEMO&q=${query}`);
            const data = await response.json();

            if (data && data.results.length > 0) {
              this.$emit('locationFound', [data.results[0].location.lat, data.results[0].location.lng]);
            } else {
              throw new Error('Location not found');
            }
          } catch (err) {
            this.errorMessage = 'Could not look up zip code';
          }

          this.loading = false;
        }
      }
    }
  }
</script>
