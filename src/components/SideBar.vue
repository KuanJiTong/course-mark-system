<template>
  <div class="sidebar">
    <i class="bi bi-x-lg" @click="$emit('toggle')"></i>
    <div class="logo-box">
      <router-link to="/home">
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

    <div class="d-flex justify-content-center align-items-end vh-100">
      
    </div>
    <hr>
    <div class="dropdown">
      <a href="#" class="d-flex align-items-center link-dark text-decoration-none dropdown-toggle" id="dropdownUser2" data-bs-toggle="dropdown" aria-expanded="false">
        <img v-if="!imageUrl" src="../assets/defaultPicture.jpg" alt="profilepic" width="32" height="32" class="rounded-circle me-2">
        <img v-else :src="imageUrl" alt="profilepic" width="32" height="32" class="rounded-circle me-2">
        <strong v-if="userSession">{{ userSession.name }}</strong>
      </a>
      <ul class="dropdown-menu text-small shadow" aria-labelledby="dropdownUser2">
        <li>  <router-link class="dropdown-item" to="/profile">Profile</router-link> </li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" @click="logout">Sign out</a></li>
      </ul>
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
  },
  data() {
    const navItems = [
      { name: "Home", link: "/home", icon: "bi bi-house-door-fill", access: "all" },
    ];

    return {
      navItems,
      user: null,
      imageUrl: null,
    };
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


