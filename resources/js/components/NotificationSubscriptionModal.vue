<template>
  <div class="fixed top-0 inset-x-0 z-10 px-4 pb-6 sm:inset-0 sm:p-0 sm:flex sm:items-center sm:justify-center">
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
        <form class="bg-white rounded-lg px-4 pt-5 pb-4 overflow-scroll shadow-xl transform max-h-screen sm:max-w-sm sm:w-full sm:p-6">
          <div>
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
              <svg class="h-6 w-6 text-green-600" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path d="M3 8L10.8906 13.2604C11.5624 13.7083 12.4376 13.7083 13.1094 13.2604L21 8M5 19H19C20.1046 19 21 18.1046 21 17V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7V17C3 18.1046 3.89543 19 5 19Z" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>

            <div class="mt-3 text-center sm:mt-5">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Set up notification
              </h3>
              <div class="mt-2">
                <p class="text-sm leading-5 text-gray-500">
                  Give us a few more details and we'll automatically text you when a desired curbside slot becomes available.
                </p>
              </div>
            </div>
          </div>

          <div class="mt-3">
            <label for="distance" class="block text-sm font-medium leading-5 text-gray-700">How far are you willing to travel?</label>
            <div class="mt-1 relative rounded-md shadow-sm">
              <input id="distance" pattern="\d*" class="form-input block w-full pr-16 sm:pr-14 sm:text-sm sm:leading-5" value="25" required />
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <span class="text-gray-500 sm:text-sm sm:leading-5">
                  miles
                </span>
              </div>
            </div>
            <p class="mt-2 text-sm text-gray-500">We will only look at stores within this radius.</p>
          </div>

          <div class="mt-4">
            <label for="distance" class="block text-sm font-medium leading-5 text-gray-700">Which stores do you want to monitor?</label>

            <div>
              <div class="mt-2 relative flex items-start">
                <div class="absolute flex items-center h-5">
                  <input name="stores[]" value="wegmans" id="store_wegmans" type="checkbox" class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out" checked>
                </div>
                <div class="pl-7 text-sm leading-5">
                  <label for="store_wegmans" class="font-medium text-gray-700">Wegmans</label>
                </div>
              </div>
              <div class="mt-3 relative flex items-start">
                <div class="absolute flex items-center h-5">
                  <input name="stores[]" value="harristeeter" id="store_harristeeter" type="checkbox" class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                </div>
                <div class="pl-7 text-sm leading-5">
                  <label for="store_harristeeter" class="font-medium text-gray-700">Harris Teeter</label>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-4">
            <label for="phone" class="block text-sm font-medium leading-5 text-gray-700">Phone Number</label>
            <div class="mt-1 relative rounded-md shadow-sm">
              <div class="absolute inset-y-0 left-0 flex items-center">
                <select aria-label="Country" class="form-select h-full py-0 pl-3 pr-7 border-transparent bg-transparent text-gray-500 sm:text-sm sm:leading-5">
                  <option>US</option>
                  <option>CA</option>
                </select>
              </div>
              <input id="phone" class="form-input block w-full pl-16 sm:text-sm sm:leading-5" placeholder="+1 (555) 987-6543" required />
            </div>

            <p class="mt-2 text-sm text-gray-500">The phone number will only be used for notifications.</p>
          </div>

          <div class="mt-4">
            <label for="distance" class="block text-sm font-medium leading-5 text-gray-700">Notify when&hellip;</label>

            <div>
              <div class="mt-2 flex items-center">
                <input id="criteria_anytime" name="form-input criteria_notifications" type="radio" class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out" checked>
                <label for="criteria_anytime" class="ml-3">
                  <span class="block text-sm leading-5 font-medium text-gray-700">Slot is available at any time</span>
                </label>
              </div>
              <div class="mt-3 flex items-center">
                <input id="criteria_soon" name="form-input criteria_notifications" type="radio" class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                <label for="criteria_soon" class="ml-3">
                  <span class="block text-sm leading-5 font-medium text-gray-700">Slot is available within the next 3 days</span>
                </label>
              </div>
              <div class="mt-3 flex items-center">
                <input id="criteria_today" name="form-input criteria_notifications" type="radio" class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                <label for="criteria_today" class="ml-3">
                  <span class="block text-sm leading-5 font-medium text-gray-700">Slot is available today</span>
                </label>
              </div>
            </div>
          </div>

          <div class="mt-5 sm:mt-6">
            <span class="flex w-full rounded-md shadow-sm">
              <button @click="submit" type="button" class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-blue-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                Notify me
              </button>
            </span>
          </div>
        </form>
    </transition>
  </div>
</template>
<script>
  export default {
    props: {
      location: {
        type: Array
      }
    },
    methods: {
      submit: function () {
        this.$emit('submit');
      }
    }
  }
</script>
