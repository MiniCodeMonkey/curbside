<template>
  <l-map
    ref="map"
    :zoom.sync="zoom"
    :center.sync="center"
    style="height: 100%; width: 100%"
  >
    <l-tile-layer
      :url="url"
      :attribution="attribution"
    />
    <l-circle
      v-if="showRadius && location"
      :lat-lng="location"
      :radius="radiusInMeters"
      color="red"
    />
    <l-geo-json
      v-if="show"
      :geojson="geojson"
      :options="options"
      :options-style="styleFunction"
    />
    <l-marker :lat-lng="marker" />
  </l-map>
</template>
<script>
  import { latLng } from 'leaflet';
  import { LMap, LTileLayer, LGeoJson, LCircle, LMarker } from 'vue2-leaflet';

  export default {
    name: 'StoreMap',
    components: {
      LMap,
      LTileLayer,
      LGeoJson,
      LCircle,
      LMarker,
    },
    props: {
      location: {
        type: Array
      },
      selectedChains: {
        type: Array
      },
      radius: {
        type: Number
      }
    },
    watch: {
      location: function(newVal, oldVal) {
        if (newVal !== null) {
          this.$refs.map.mapObject.flyTo(latLng(this.location), 9, {
            animate: true,
            duration: 0.5
          });

          // Show radius circle after animation is complete
          setTimeout(() => {
            this.showRadius = true;
          }, 500);
        }
      }
    },
    data() {
      return {
        zoom: 4,
        center: [31.34, -99.32],
        loading: false,
        showRadius: false,
        show: true,
        enableTooltip: true,
        geojson: null,
        fillColor: "#e4ce7f",
        url: 'https://tile-cdn.geocod.io/tiles/geocodio/{z}/{x}/{y}.png',
        attribution:
          '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
        marker: latLng(47.41322, -1.219482)
      };
    },
    computed: {
      radiusInMeters() {
        return this.radius * 1609.34;
      },
      options() {
        return {
          onEachFeature: this.onEachFeatureFunction
        };
      },
      styleFunction() {
        const fillColor = this.fillColor; // important! need touch fillColor in computed for re-calculate when change fillColor
        return () => {
          return {
            weight: 2,
            color: "#ECEFF1",
            opacity: 1,
            fillColor: fillColor,
            fillOpacity: 1
          };
        };
      },
      onEachFeatureFunction() {
        if (!this.enableTooltip) {
          return () => {};
        }
        return (feature, layer) => {
          layer.bindTooltip(
            "<div>code:" +
              feature.properties.code +
              "</div><div>nom: " +
              feature.properties.nom +
              "</div>",
            { permanent: false, sticky: true }
          );
        };
      }
    },
    async created() {
      this.loading = true;
      const response = await fetch("https://rawgit.com/gregoiredavid/france-geojson/master/regions/pays-de-la-loire/communes-pays-de-la-loire.geojson")
      const data = await response.json();
      this.geojson = data;
      this.loading = false;
    }
  }
</script>
