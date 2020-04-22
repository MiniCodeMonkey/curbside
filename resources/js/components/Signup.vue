<template>
<div>
  <div class="flex flex-row h-screen">
    <div class="w-1/3">
      <subscribe-form :availableChains="chains"></subscribe-form>
    </div>

    <div class="w-2/3">
      <store-map :location="location"></store-map>
    </div>
  </div>
  <location-modal v-if="!location" @locationFound="saveLocation"></location-modal>
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
        location: null,
      };
    },
    created() {
      // TODO: Remove me
      setTimeout(() => {
        this.location = [38.8, -76.9];
      }, 250);
    },
    methods: {
      saveLocation(location) {
        this.location = location;
      },
    }
  }
</script>
