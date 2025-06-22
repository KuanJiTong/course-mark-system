<template>
  <div>
    <NavBar @toggle="toggleSideBar" />
    <aside v-show="sideBarOpen" class="advisor-sidebar">
      <i class="bi bi-x-lg close-btn" @click="toggleSideBar"></i>
      <div class="logo-box">
        <router-link to="/advisor-dashboard">
          <img src="../assets/logo CMMS.png" alt="Logo" class="logo" />
        </router-link>
      </div>
      <nav>
        <router-link v-for="item in advisorNav" :key="item.link" :to="item.link" class="sidebar-link" active-class="active">
          <span v-if="item.icon" :class="['sidebar-icon', item.icon]"></span>
          <span>{{ item.name }}</span>
        </router-link>
         <router-link to="/advisor/advisee-overall-performance" class="sidebar-link" active-class="active-link">
          <span>Advisee Overall Performance</span>
        </router-link>
      </nav>
    </aside>
    <main :class="['content-container', { 'shrink': sideBarOpen }]">
      <router-view />
    </main>
  </div>
</template>

<script>
import NavBar from './NavBar.vue';
export default {
  name: 'AdvisorDashboardLayout',
  components: { NavBar },
  data() {
    return {
      sideBarOpen: false,
      advisorNav: [
        { name: "Dashboard", link: "/advisor-dashboard", icon: "bi bi-speedometer2" },
        { name: "Advisee List", link: "/advisor-advisees", icon: "bi bi-people" },
        { name: "Compare with Coursemates", link: "/advisor-compare", icon: "bi bi-bar-chart" },
        { name: "View Ranking/Position", link: "/advisor-rank", icon: "bi bi-trophy" },
        { name: "Class Average per Component", link: "/advisor-component-averages", icon: "bi bi-graph-up" },
        { name: "Student Feedback/Remarks", link: "/advisor-student-remarks", icon: "bi bi-chat-left-text" },
        // { name: "Student Progress", link: "/advisor-progress", icon: "bi bi-bar-chart-line" },
        // { name: "Course Overview", link: "/advisor-courses", icon: "bi bi-journal-bookmark" },
        // { name: "Remark Requests", link: "/advisor-remarks", icon: "bi bi-chat-dots" },
      ]
    }
  },
  methods: {
    toggleSideBar() {
      this.sideBarOpen = !this.sideBarOpen;
    },
  },
}
</script>

<style scoped>
.advisor-sidebar {
  display: flex;
  top: 0;
  flex-direction: column;
  flex-shrink: 0;
  padding: 1rem;
  background-color: #fff;
  height: 100vh;
  box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
  width: 280px;
  position: fixed;
  z-index: 100;
}
.logo-box {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 2rem;
}
.logo {
  width: 180px;
}
nav {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.sidebar-link {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  color: #222;
  font-weight: 500;
  text-decoration: none;
  transition: background 0.15s, color 0.15s;
}
.sidebar-link.active, .sidebar-link.router-link-exact-active {
  background: #0d6efd;
  color: #fff;
}
.sidebar-link:hover {
  background: #e3f0ff;
  color: #0d6efd;
}
.sidebar-icon {
  font-size: 1.3em;
}
.content-container {
  width: 100%;
  max-width: 100vw;
  margin-left: 0;
  padding: 50px;
  transition: margin-left 0.2s, width 0.2s;
}
.content-container.shrink {
  width: calc(100% - 280px);
  margin-left: 280px;
}
</style> 