<template>
<div>
  <div class="flex flex-row h-screen">
    <div class="w-1/3">
      <subscribe-form
        :errorMessage="errorMessage"
        :availableChains="chains"
        :selectedChains="selectedChains"
        :radius="radius"
        @selectedChainsChanged="selectedChains = $event"
        @radiusChanged="radius = $event"
        @submit="handleSubmit"
      ></subscribe-form>
    </div>

    <div class="w-2/3">
      <store-map
        :location="location"
        :selectedChains="selectedChains"
        :radius="radius"
      ></store-map>
    </div>
  </div>
  <location-modal v-if="!location" @locationFound="saveLocation"></location-modal>
  <saving-modal v-if="isSaving || subscriptionResponse" :isSaving="isSaving" :subscriptionResponse="subscriptionResponse"></saving-modal>
</div>
</template>
<script>
  export default {
    props: {
      chains: {
        type: Array
      }
    },
    data() {
      return {
        isSaving: false,
        errorMessage: null,
        subscriptionResponse: null,

        location: null,
        radius: 25,
        selectedChains: []
      };
    },
    created() {
      // TODO: Remove me
      setTimeout(() => {
        //this.location = [38.8, -76.9];
      }, 250);
    },
    methods: {
      saveLocation(location) {
        this.location = location;
      },
      handleSubmit: function (radius, chains, phone, criteria) {
        window.scrollTo(0, 0);
        this.isSaving = true;

        axios.post('subscribe', {
          radius,
          chains,
          phone,
          criteria,
          location: this.location
        }).then(response => {
          this.subscriptionResponse = response.data;
          this.isSaving = false;
        }).catch(err => {
          this.isSaving = false;

          if (err.response && err.response.data && err.response.data.errors) {
            const errors = err.response.data.errors;
            this.errorMessage = errors[Object.keys(errors)[0]].join(' ');
          } else {
            this.errorMessage = err.message;
          }
        })
      }
    }
  }
</script>
