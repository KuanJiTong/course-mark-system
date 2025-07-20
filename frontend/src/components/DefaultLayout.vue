<template>
  <div>
    <NavBar @toggle="toggleSideBar" />
    <SideBar
      v-show="sideBarOpen"
      @toggle="toggleSideBar"
      :activeTab="activeTab"
    />
    <main :class="['content-container', { shrink: sideBarOpen }]">
      <router-view @update-active-tab="setActiveTab" />
    </main>
  </div>
</template>

<script>
import SideBar from '@/components/SideBar.vue';
import NavBar from '@/components/NavBar.vue';

export default {
  components: { SideBar, NavBar },
  data() {
    return {
      sideBarOpen: false,
      activeTab: '',
    };
  },
  watch: {
    $route() {
      this.activeTab = '';
    },
  },
  methods: {
    toggleSideBar() {
      this.sideBarOpen = !this.sideBarOpen;
    },
    setActiveTab(tabName) {
      this.activeTab = tabName;
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
