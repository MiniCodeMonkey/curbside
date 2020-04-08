<template>
  <form class="bg-white rounded-lg px-4 pt-5 pb-4 overflow-hidden shadow-xl transform sm:max-w-sm sm:w-full sm:p-6">
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

    <div v-if="errorMessage" class="mt-3 p-2 bg-red-600 rounded-md text-white text-base sm:text-sm">
        {{ errorMessage }}
    </div>

    <div class="mt-3">
      <label for="radius" class="block text-sm font-medium leading-5 text-gray-700">How far are you willing to travel?</label>
      <div class="mt-1 relative rounded-md shadow-sm">
        <input id="radius" v-model="radius" class="form-input block w-full pr-16 sm:pr-14 sm:text-sm sm:leading-5" pattern="\d*" required />
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
          <span class="text-gray-500 sm:text-sm sm:leading-5">
            miles
          </span>
        </div>
      </div>
      <p class="mt-2 text-sm text-gray-500">We will only look at stores within this radius.</p>
    </div>

    <div class="mt-4">
      <label for="chains" class="block text-sm font-medium leading-5 text-gray-700">Which stores do you want to monitor?</label>

      <div>
        <div v-for="(chainName, index) in availableChains" class="mt-3 relative flex items-start">
          <div class="absolute flex items-center h-5">
            <input name="chains" v-model="chains" :value="chainName" :id="'store_' + index" type="checkbox" class="form-checkbox h-4 w-4 text-orange-600 transition duration-150 ease-in-out">
          </div>
          <div class="pl-7 text-sm leading-5">
            <label :for="'store_' + index" class="font-medium text-gray-700">{{ chainName }}</label>
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
          </select>
        </div>
        <input id="phone" v-model="phone" class="form-input block w-full pl-16 sm:text-sm sm:leading-5" placeholder="(555) 987-6543" required />
      </div>

      <p class="mt-2 text-sm text-gray-500">The phone number will only be used for notifications.</p>
    </div>

    <div class="mt-4">
      <label class="block text-sm font-medium leading-5 text-gray-700">Notify when&hellip;</label>

      <div>
        <div class="mt-2 flex items-center">
          <input id="criteria_anytime" v-model="criteria" name="form-input criteria_notifications" value="ANYTIME" type="radio" class="form-radio h-4 w-4 text-orange-600 transition duration-150 ease-in-out" checked>
          <label for="criteria_anytime" class="ml-3">
            <span class="block text-sm leading-5 font-medium text-gray-700">Slot is available at any time</span>
          </label>
        </div>
        <div class="mt-3 flex items-center">
          <input id="criteria_soon" v-model="criteria" name="form-input criteria_notifications" value="SOON" type="radio" class="form-radio h-4 w-4 text-orange-600 transition duration-150 ease-in-out">
          <label for="criteria_soon" class="ml-3">
            <span class="block text-sm leading-5 font-medium text-gray-700">Slot is available within the next 3 days</span>
          </label>
        </div>
        <div class="mt-3 flex items-center">
          <input id="criteria_today" v-model="criteria" name="form-input criteria_notifications" value="TODAY" type="radio" class="form-radio h-4 w-4 text-orange-600 transition duration-150 ease-in-out">
          <label for="criteria_today" class="ml-3">
            <span class="block text-sm leading-5 font-medium text-gray-700">Slot is available today</span>
          </label>
        </div>
      </div>
    </div>

    <div class="mt-5 sm:mt-6">
      <span class="flex w-full rounded-md shadow-sm">
        <button @click="submit" type="button" class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-orange-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-orange-500 focus:outline-none focus:border-orange-700 focus:shadow-outline-orange transition ease-in-out duration-150 sm:text-sm sm:leading-5">
          Notify me
        </button>
      </span>
    </div>
  </form>
</template>
<script>
  export default {
    props: {
      errorMessage: {
        type: String
      }
    },
    data() {
      return {
        radius: 25,
        chains: ['Wegmans'],
        phone: '',
        criteria: 'ANYTIME',
        availableChains: [
          "Wegmans",
          "Harris Teeter",
          "H-E-B",
          "Kroger",
          "City Market",
          "Copps",
          "Dillons",
          "Fred Meyer",
          "Fry's",
          "Gerbes",
          "JayC Foods Stores",
          "King Soopers",
          "Metro Market",
          "Owen's Market",
          "Pick 'n Save",
          "QFC",
          "Ralphs",
          "Smith's",
        ]
      };
    },
    methods: {
      submit: function () {
        this.$emit('submit', this.radius, this.chains, this.phone, this.criteria);
      }
    }
  }
</script>
