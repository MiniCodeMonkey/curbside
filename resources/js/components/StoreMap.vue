<template>
  <l-map
    ref="map"
    :zoom.sync="zoom"
    :center.sync="center"
    :options="mapOptions"
    class="h-screen"
  >
    <l-tile-layer
      :url="url"
      :attribution="attribution"
    />
    <l-geo-json
      v-if="geojson"
      :geojson="geojson"
      :options="markerOptions"
    />
    <l-circle
      v-if="showRadius && location"
      :lat-lng="location"
      :radius="radiusInMeters"
      color="transparent"
    />
  </l-map>
</template>
<script>
  import { uniq } from 'lodash';
  import latLngGeodesy from 'geodesy/latlon-spherical.js';
  import { latLng, circleMarker, CRS } from 'leaflet';
  import { LMap, LTileLayer, LGeoJson, LCircle } from 'vue2-leaflet';

  export default {
    name: 'StoreMap',
    components: {
      LMap,
      LTileLayer,
      LGeoJson,
      LCircle,
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
            this.updateInRangeStoreIds();
          }, 500);
        }
      },
      radius: function() {
        this.updateInRangeStoreIds();
      }
    },
    methods: {
      updateInRangeStoreIds() {
        const userLocation = this.location && new latLngGeodesy(this.location[0], this.location[1]);
        const radiusInMeters = this.radiusInMeters;

        if (userLocation && this.geojson) {
          const inRangeFeatures = this.geojson.features.filter(feature => {
            const storeLocation = new latLngGeodesy(
              feature.geometry.coordinates[1],
              feature.geometry.coordinates[0]
            );

            return storeLocation.distanceTo(userLocation) <= radiusInMeters;
          })

          this.inRangeStoreIds = inRangeFeatures.map(feature => feature.id);
          this.inRangeChains = uniq(inRangeFeatures.map(feature => feature.properties.chain));
        } else {
          this.inRangeStoreIds = [];
          this.inRangeChains = [];
        }

        this.$emit('inRangeChainsChanged', this.inRangeChains);
      }
    },
    data() {
      return {
        mapOptions: {
          preferCanvas: true
        },
        zoom: 4,
        center: [31.34, -99.32],
        loading: false,
        showRadius: false,
        geojson: null,
        url: 'https://tile-cdn.geocod.io/tiles/geocodio/{z}/{x}/{y}.png',
        attribution:
          '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
        inRangeStoreIds: [],
        inRangeChains: []
      };
    },
    computed: {
      radiusInMeters() {
        return this.radius * 1609.34;
      },
      markerOptions() {
        return {
          onEachFeature: this.onEachFeatureFunction,
          filter: this.geoJsonFilterFunction,
          pointToLayer: this.pointToLayerFunction,
        };
      },
      pointToLayerFunction() {
        return (feature, latlng) => {
          return circleMarker(latlng, {
            color: feature.properties.color,
            fill: true,
            radius: 5
          });
        }
      },
      onEachFeatureFunction() {
        return (feature, layer) => {
          layer.bindTooltip(
            `${feature.properties.chain} ${feature.properties.name}`,
            { permanent: false, sticky: true }
          );
        };
      },
      geoJsonFilterFunction() {
        const inRangeStoreIds = this.inRangeStoreIds;
        const selectedChains = this.selectedChains;

        return feature => selectedChains.includes(feature.properties.chain)
           && inRangeStoreIds.includes(feature.id)
      }
    },
    async created() {
      this.loading = true;
      const response = await fetch(`${window.__ASSET_URL__}stores.geojson`);
      const data = await response.json();
      this.geojson = data;
      this.loading = false;
    }
  }
</script>
