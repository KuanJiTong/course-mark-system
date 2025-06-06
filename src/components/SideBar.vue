<template>
  <div tabindex="0" class="sidebar" @focusout="$emit('closeSideBar')">
    <div class="logo-box">
      <router-link to="/home">
        <img src="../assets/logo CMMS.png" alt="Logo" class="logo" />
      </router-link>
    </div>
    
    <hr>
      
    <div>
      <SideBarTab 
        v-for="(item,index) in filteredNavItems"
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
      { name: "Home", link: "/", icon: "home" },
      { name: "My ShareLinks", link: "/my_sharelinks", icon: "mysharelinks" },
      { name: "Shared With Me", link: "/shared_with_me", icon: "sharewithme" },
      { name: "Favourites", link: "/favourites", icon: "favourites" },
      { name: "Notification", link: "/notification", icon: "notification" },
      { name: "Group", link: "/groups", icon: "group" },
      {
        name: "Resource Management",
        link: "/admin/AllResources",
        icon: "mysharelinks",
        role: "Admin",
      },
      { name: 'Category', 
        link: '/admin/category', 
        icon: 'category', 
        role: 'Admin' },
      {
        name: "User Log",
        link: "/admin/UserLog",
        icon: "sharewithme",
        role: "Admin",
      },
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
</script>

<style scoped>
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


