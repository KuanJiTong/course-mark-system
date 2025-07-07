<template>
  <div class="sidebar">
    <i class="bi bi-x-lg" @click="$emit('toggle')"></i>
    <div class="logo-box">
      <router-link to="/">
        <img src="../assets/logo CMMS.png" alt="Logo" class="logo" />
      </router-link>
    </div>
    
    <hr>
      
    <div style=" overflow-y: scroll;">
      <SideBarTab 
        v-for="(item,index) in filteredNavItems"
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
      { name: "Performance Trend", link: "/performance-trend", icon: "bi bi-bar-chart-line", access: "lecturer" },
      { name: "View Mark Breakdown", link: "/view-mark-breakdown", icon: "bi bi-list-columns-reverse", access: "lecturer" },

      // Student
      { name: "My Marks", link: "/student-marks", icon: "bi bi-clipboard-data", access: "student" },
      { name: "Progress & Breakdown", link: "/student-progress", icon: "bi bi-bar-chart-line", access: "student" },
      { name: "Compare with Coursemates", link: "/student-compare", icon: "bi bi-people-fill", access: "student" },
      { name: "Class Rank & Percentile", link: "/student-rank", icon: "bi bi-trophy", access: "student" },
      { name: "What-If Simulator", link: "/student-whatif", icon: "bi bi-sliders", access: "student" },
      { name: "Submit Remark Request", link: "/student-remark", icon: "bi bi-chat-dots", access: "student" },

      //AA
      { name: "My Advisees", link: "/advisor-advisees", icon: "bi bi-people-fill", access: "advisor" },
      { name: "Advisee Marks", link: "/advisor-advisee-marks", icon: "bi bi-journal-check", access: "advisor" },
      { name: "Compare Advisees", link: "/advisor-compare", icon: "bi bi-bar-chart-line", access: "advisor" },
      { name: "Advisee Ranking", link: "/advisor-rank", icon: "bi bi-trophy", access: "advisor" },
      { name: "Component Averages", link: "/advisor-component-averages", icon: "bi bi-graph-up", access: "advisor" },
      { name: "Student Remarks", link: "/advisor-student-remarks", icon: "bi bi-chat-square-text", access: "advisor" },
      { name: "Overall Performance", link: "/advisor/advisee-overall-performance", icon: "bi bi-speedometer2", access: "advisor" }
    ];

    // Get user info from sessionStorage
    const user = JSON.parse(sessionStorage.getItem('user'));
    const userRoles = user?.roles?.map(r => r.toLowerCase()) || [];

    return {
      navItems,
      user,
      userRoles
    };
  },
  computed: {
    filteredNavItems() {
      return this.navItems.filter(item => {
        if (!item.access) return false;

        if (item.access === 'all') return true;

        if (typeof item.access === 'string') {
          return this.userRoles.includes(item.access.toLowerCase());
        }

        if (Array.isArray(item.access)) {
          const accessRoles = item.access.map(r => r.toLowerCase());
          return this.userRoles.some(role => accessRoles.includes(role));
        }

        return false;
      });
    },

    hasSidebarAccess() {
      const allowedRoles = ['admin', 'lecturer', 'student'];
      return this.userRoles.some(role => allowedRoles.includes(role));
    }
  }
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


