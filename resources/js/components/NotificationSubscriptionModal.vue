<template>
  <div class="absolute top-0 inset-x-0 z-10 px-4 pb-6 sm:inset-0 sm:p-0 sm:flex sm:items-center sm:justify-center">
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
        <subscribe-form v-if="!subscriptionResponse && !isSaving" :errorMessage="errorMessage" @submit="handleSubmit"></subscribe-form>
        <subscription-saving v-if="isSaving"></subscription-saving>
        <subscription-created v-if="subscriptionResponse" :subscription="subscriptionResponse" @dismiss="dismiss"></subscription-created>
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
    data() {
      return {
        isSaving: false,
        errorMessage: null,
        subscriptionResponse: null
      };
    },
    methods: {
      handleSubmit: function (radius, chains, phone, criteria) {
        axios.post('subscribe', {
          radius,
          chains,
          phone,
          criteria,
          location: this.location
        }).then(response => {
          this.subscriptionResponse = response.data;
        }).catch(err => {
          if (err.response && err.response.data && err.response.data.errors) {
            const errors = err.response.data.errors;
            this.errorMessage = errors[Object.keys(errors)[0]].join(' ');
          } else {
            this.errorMessage = err.message;
          }
        })
      },
      dismiss: function () {
        this.$emit('dismiss');
      }
    }
  }
</script>
