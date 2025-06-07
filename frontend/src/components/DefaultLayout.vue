<template>
  <div>
    <NavBar @toggle="toggleSideBar"/>
    <SideBar v-show="sideBarOpen" @toggle="toggleSideBar" :activeTab="activeTab" ref="sidebar"/>
    <main :class="['content-container', { 'shrink': sideBarOpen }]">
      <router-view />
    </main>
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
.content-container {
  width: 100%;               
  max-width: 100vw;         
  margin-left: 0; 
  padding: 50px;           
}

.content-container.shrink {
  width: calc(100% - 280px); 
  margin-left: 280px;        
}
</style>
