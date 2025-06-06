<template>
  <div>
    <NavBar @toggle="openSideBar"/>
      <!-- Sidebar -->
      <SideBar v-show="sideBarOpen" @closeSideBar="closeSideBar" :activeTab="activeTab" ref="sidebar"/>
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
    openSideBar() {
      this.sideBarOpen = true;
    },
    closeSideBar(){
      console.log('hi');
      this.sideBarOpen = false;
    }
  },
};
</script>

<style scoped>

</style>
