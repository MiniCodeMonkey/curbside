<template>
  <form class="bg-white rounded-lg px-4 pt-5 pb-4 overflow-scroll shadow-xl transform max-h-screen sm:max-w-sm sm:w-full sm:p-6">
    <div>
      <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
        <svg class="h-6 w-6 text-green-600" stroke="currentColor" fill="none" viewBox="0 0 24 24">
          <path d="M3 8L10.8906 13.2604C11.5624 13.7083 12.4376 13.7083 13.1094 13.2604L21 8M5 19H19C20.1046 19 21 18.1046 21 17V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7V17C3 18.1046 3.89543 19 5 19Z" stroke="#4A5568" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>

      <div class="mt-3 text-center sm:mt-5">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
          Subscription created
        </h3>
        <div class="mt-2">
          <p class="text-sm leading-5 text-gray-500">
            We are now monitoring
            <strong>{{ subscription.count === 1 ? `${subscription.count} store` : `${subscription.count} stores`}}</strong>
            for you and will text you as soon as a desired slot opens up.
          </p>
        </div>
      </div>

      <div class="mt-5 sm:mt-6">
        <span class="flex w-full rounded-md shadow-sm">
          <a href="/" class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-orange-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-orange-500 focus:outline-none focus:border-orange-700 focus:shadow-outline-orange transition ease-in-out duration-150 sm:text-sm sm:leading-5">
            Close
          </a>
        </span>
      </div>
    </div>
  </form>
</template>
<script>
  export default {
    props: {
      subscription: {
        type: Object
      }
    }
  }
</script>
