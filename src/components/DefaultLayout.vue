<template>
  <div>
    <NavBar @toggle="toggleSideBar"/>
      <!-- Sidebar -->
      <SideBar v-show="sideBarOpen" @toggle="toggleSideBar" :activeTab="activeTab" ref="sidebar"/>
      <!-- Main Content Area (Slot for dynamic content) -->
    <slot></slot> <!-- Content will be injected here -->
  </div>
</template>
  
<script>
import SideBar from './SideBar.vue'; 
import NavBar from './NavBar.vue';

export default {
  components: {
    SideBar,
    NavBar
  },
  props:{
    activeTab: {
      type: String,
    }
  },
  data() {
    return {
      sideBarOpen: false,
    };
  },
  methods: {
    toggleSideBar() {
      this.sideBarOpen = !this.sideBarOpen;
    },
  },
};
</script>

<style scoped>

</style>
