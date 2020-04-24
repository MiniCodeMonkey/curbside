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
        <subscription-saving v-if="isSaving"></subscription-saving>
        <subscription-created v-if="subscriptionResponse" :subscription="subscriptionResponse"></subscription-created>
    </transition>
  </div>
</template>
<script>
  export default {
    props: {
      isSaving: {
        type: Boolean
      },
      subscriptionResponse: {
        type: Object
      }
    }
  }
</script>
