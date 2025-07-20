<template>
  <div class="sidebar">
    <i class="bi bi-x-lg" @click="$emit('toggle')"></i>
    <div class="logo-box">
      <router-link to="/">
        <img src="../assets/logo CMMS.png" alt="Logo" class="logo" />
      </router-link>
    </div>

    <hr />

  <div :style="(userRoles.includes('lecturer') && userRoles.includes('advisor')) ? { overflowY: 'scroll' } : {}">
      <!-- Common Items -->
      <SideBarTab
        v-for="(item, index) in commonItems"
        :key="'common-' + index"
        :item="item"
        :isActive="item.name === activeTab"
      />

      <!-- Lecturer Section -->
      <div v-if="isLecturer">
        <hr v-if="isAdvisor"/>
        <h5 class="text-muted px-3" v-if="isAdvisor">Lecturer</h5>
        <SideBarTab
          v-for="(item, index) in lecturerItems"
          :key="'lecturer-' + index"
          :item="item"
          :isActive="item.name === activeTab"
        />
      </div>

      <!-- Advisor Section -->
      <div v-if="isAdvisor">
        <hr v-if="isLecturer"/>
        <h5 class="text-muted px-3" v-if="isLecturer">Academic Advisor</h5>
        <SideBarTab
          v-for="(item, index) in advisorItems"
          :key="'advisor-' + index"
          :item="item"
          :isActive="item.name === activeTab"
        />
      </div>

      <!-- Student Section -->
      <div v-if="isStudent">
        <SideBarTab
          v-for="(item, index) in studentItems"
          :key="'student-' + index"
          :item="item"
          :isActive="item.name === activeTab"
        />
      </div>

      <!-- Admin Section -->
      <div v-if="isAdmin">
        <SideBarTab
          v-for="(item, index) in adminItems"
          :key="'admin-' + index"
          :item="item"
          :isActive="item.name === activeTab"
        />
      </div>
    </div>
  </div>
</template>

<script>
import SideBarTab from "./SideBarTab.vue";

export default {
  components: {
    SideBarTab,
  },
  props: {
    activeTab: String
  },
  data() {
    const navItems = [
      { name: "Home", link: "/", icon: "bi bi-house-door-fill", access: "all" },

      // Admin
      { name: "Course", link: "/course-management", icon: "bi bi-book-fill", access: "admin" },
      { name: "User", link: "/user-management", icon: "bi bi-person-fill", access: "admin" },

      // Lecturer
      { name: "My Courses", link: "/lecturer-course-management", icon: "bi bi-journal-bookmark-fill", access: "lecturer" },
      { name: "Component Marks", link: "/manage-component-marks", icon: "bi bi-clipboard-data", access: "lecturer" },
      { name: "Final Exam Entry", link: "/add-final-exam-marks", icon: "bi bi-pencil-square", access: "lecturer" },
      { name: "View Mark Breakdown", link: "/view-mark-breakdown", icon: "bi bi-list-columns-reverse", access: "lecturer" },
      { name: "Remark Request", link: "/remark-requests", icon: "bi bi-chat-dots", access: "lecturer" },

      // Student
      { name: "My Marks", link: "/student-marks", icon: "bi bi-clipboard-data", access: "student" },
      { name: "Compare with Coursemates", link: "/student-compare", icon: "bi bi-people-fill", access: "student" },
      { name: "Class Rank & Percentile", link: "/student-rank", icon: "bi bi-trophy", access: "student" },
      { name: "Component average", link: "/student-component-averages", icon: "bi bi-graph-up-arrow", access: "student" },
      { name: "Submit Remark Request", link: "/student-remark", icon: "bi bi-chat-dots", access: "student" },

      // Advisor
      { name: "My Advisees", link: "/advisor-advisees", icon: "bi bi-people-fill", access: "advisor" },
      { name: "Compare Advisees", link: "/advisor-compare", icon: "bi bi-bar-chart-line", access: "advisor" },
      { name: "Advisee Ranking", link: "/advisor-rank", icon: "bi bi-trophy", access: "advisor" },
      { name: "Component Averages", link: "/advisor-component-averages", icon: "bi bi-graph-up", access: "advisor" },
      { name: "Student Remarks", link: "/advisor-student-remarks", icon: "bi bi-chat-square-text", access: "advisor" },
      { name: "Overall Performance", link: "/advisor/advisee-overall-performance", icon: "bi bi-speedometer2", access: "advisor" }
    ];

    const user = JSON.parse(sessionStorage.getItem("user"));
    const userRoles = user?.roles?.map(r => r.toLowerCase()) || [];

    return {
      navItems,
      user,
      userRoles,
    };
  },
  computed: {
    isLecturer() {
      return this.userRoles.includes("lecturer");
    },
    isAdvisor() {
      return this.userRoles.includes("advisor");
    },
    isAdmin() {
      return this.userRoles.includes("admin");
    },
    isStudent() {
      return this.userRoles.includes("student");
    },

    commonItems() {
      return this.navItems.filter(item => item.access === "all");
    },
    lecturerItems() {
      return this.navItems.filter(item => item.access === "lecturer" && this.isLecturer);
    },
    advisorItems() {
      return this.navItems.filter(item => item.access === "advisor" && this.isAdvisor);
    },
    adminItems() {
      return this.navItems.filter(item => item.access === "admin" && this.isAdmin);
    },
    studentItems() {
      return this.navItems.filter(item => item.access === "student" && this.isStudent);
    }
  }
};
</script>


<style scoped>
.bi-x-lg{
  position: absolute;
  cursor: pointer;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background-color: transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.3s ease;
}

.bi-x-lg:hover {
  background-color: #b5b3b368; 
}

.bi-x-lg:active {
  transform: scale(0.9); 
  background-color: #e0e0e0; 
}

.sidebar {
  display: flex;
  top: 0;
  flex-direction: column;
  flex-shrink: 0;
  padding: 1rem; 
  background-color: #f8f9fa; 
  height: 100vh;
  box-shadow: 0 .5rem 1rem rgba(0,0,0,.15); 
  width: 280px;
  position: fixed;
}

.bottom-div {
  position: absolute;
  bottom: 0; 
  width: 100%; 
}

.logo-box{
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo{
  width:180px;
}
</style>


