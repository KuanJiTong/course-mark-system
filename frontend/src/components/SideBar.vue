<template>
  <div class="sidebar">
    <i class="bi bi-x-lg" @click="$emit('toggle')"></i>
    <div class="logo-box">
      <router-link to="/">
        <img src="../assets/logo CMMS.png" alt="Logo" class="logo" />
      </router-link>
    </div>
    
    <hr>
      
    <div>
      <SideBarTab 
        v-for="(item,index) in navItems"
        :key="index"
        :item="item"
        :isActive="item.name === activeTab"
      />
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
    activeTab: String,
     userRole: {
      type: String,
      required: true
    },
     allowedRoles: {
      type: Array,
      default: () => ['admin', 'lecturer', 'student']
    }
  },
  data() {
    const navItems = [
      { name: "Home", link: "/", icon: "bi bi-house-door-fill", access: "all" },

      // Admin
      // { name: "Dashboard", link: "/admin-dashboard", icon: "bi bi-speedometer2", access: "admin" },
      { name: "Course", link: "/course-management", icon: "bi bi-book-fill", access: "admin" },
      { name: "User", link: "/user-management", icon: "bi bi-person-fill", access: "admin" },

      // Lecturer
      // { name: "Dashboard", link: "/lecturer-dashboard", icon: "bi bi-speedometer2", access: "lecturer" },
      { name: "My Courses", link: "/lecturer-course-management", icon: "bi bi-journal-bookmark-fill", access: "lecturer" },
      { name: "Manage Students", link: "/lecturer-course-management/students/1", icon: "bi bi-people-fill", access: "lecturer" },
      { name: "Component Marks", link: "/manage-component-marks", icon: "bi bi-clipboard-data", access: "lecturer" },
      { name: "Final Exam Entry", link: "/add-final-exam-marks", icon: "bi bi-pencil-square", access: "lecturer" },
      { name: "Mark Breakdown", link: "/mark-breakdown", icon: "bi bi-graph-up", access: "lecturer" },
      { name: "Performance Trend", link: "/performance-trend", icon: "bi bi-bar-chart-line", access: "lecturer" },
      { name: "View Mark Breakdown", link: "/view-mark-breakdown", icon: "bi bi-list-columns-reverse", access: "lecturer" },
      { name: "Component Mark Detail", link: "/component-marks/1", icon: "bi bi-clipboard-check", access: "lecturer" },
      // // Student
      // { name: "Dashboard", link: "/student-dashboard", icon: "bi bi-speedometer2", access: "student" },
      // { name: "My Marks", link: "/student-marks", icon: "bi bi-clipboard-data", access: "student" },
      // { name: "Progress & Breakdown", link: "/student-progress", icon: "bi bi-bar-chart-line", access: "student" },
      // { name: "Compare with Coursemates", link: "/student-compare", icon: "bi bi-people-fill", access: "student" },
      // { name: "Class Rank & Percentile", link: "/student-rank", icon: "bi bi-trophy", access: "student" },
      // { name: "What-If Simulator", link: "/student-whatif", icon: "bi bi-sliders", access: "student" },
      // { name: "Submit Remark Request", link: "/student-remark", icon: "bi bi-chat-dots", access: "student" },

    ];

    return {
      navItems,
      user: null,
      imageUrl: null,
    };
  },
  computed: {
    filteredNavItems() {
      return this.navItems.filter(item => {
        if (item.access === 'all') return true;
        if (Array.isArray(item.access)) {
          return item.access.includes(this.userRole);
        }
        return item.access === this.userRole;
      });
    },
    hasSidebarAccess() {
      return this.allowedRoles.includes(this.userRole);
    }
  },
  methods: {
    
  },
};
// (Lecturer)
// Manage Students
// Manage Assessments
// Final Exam Entry
// Mark Summary
// Student Analytics
// Export Results
// Notify Students
// View Remark Requests

// (Student)
// Dashboard
// My Marks
// Progress & Breakdown
// Compare with Coursemates
// Class Rank & Percentile
// What-If Simulator
// Submit Remark Request

// (AA)
// My Advisees
// View Student Marks
// At-Risk Students
// Consultation Notes
// Export Reports

// (Admin)
// Manage Users
// Assign Lecturers
// System Logs
// Reset Passwords
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


