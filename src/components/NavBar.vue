<template>
  <nav>
    <ul class="navbar-ul">
        <li class="nav-items">
            <i class="bi bi-list" @click="$emit('toggle')"></i>
        </li>
        <li class="nav-items">
            <router-link to="/home">
            <img src="../assets/logo CMMS.png" alt="Logo" class="logo" />
            </router-link>
        </li>
        <li class="nav-items dropdown" @click="toggleDropdown" @focusout="closeDropdown" tabindex="0">
            <a href="#" class="d-flex align-items-center link-dark text-decoration-none dropdown-toggle">
                <img
                    v-if="!imageUrl"
                    src="../assets/defaultPicture.jpg"
                    alt="profilepic"
                    width="32"
                    height="32"
                    class="rounded-circle me-2"
                />
                <img
                    v-else
                    :src="imageUrl"
                    alt="profilepic"
                    width="32"
                    height="32"
                    class="rounded-circle me-2"
                />
                <strong v-if="userSession">{{ userSession.name }}</strong>
            </a>
        <ul
          class="dropdown-menu text-small shadow"
          v-show="isDropdownOpen"
        >
            <li>
                <router-link class="dropdown-item" to="/profile">Profile</router-link>
            </li>
            <li>
                <hr class="dropdown-divider" />
            </li>
            <li>
                <router-link class="dropdown-item" @click="logout">Sign out</router-link>
            </li>
        </ul>
      </li>
    </ul>
  </nav>
</template>

<script>
export default {
  data() {
    return {
      imageUrl: null,
      isDropdownOpen: false,
      userSession: { name: "User" },
    };
  },
  methods: {
    toggleDropdown() {
      this.isDropdownOpen = !this.isDropdownOpen;
    },
    closeDropdown() {
      this.isDropdownOpen = false;
    },
    logout() {
      console.log("Logged out");
    },
  },
};
</script>

<style scoped>
.dropdown-menu {
  position: absolute;
  background-color: white;
  top: 80%;
  right: 10px;
  display: block;
  z-index: 1000;
  padding: 0.5rem 0;
  margin-top: 0.5rem;
  border: 1px solid rgba(0, 0, 0, 0.15);
  border-radius: 0.25rem;
  min-width: 10rem;
}

nav{
    background-color: white;
    box-shadow: 3px 3px 5px rgba(0,0,0,0.1);
}

.navbar-ul{
    width: 100%;
    list-style: none;
    display: flex;
    justify-content: flex-start;
    align-items: center;
}

.nav-items{
    height: 50px;
    display: flex;
    justify-content: flex-start;
    align-items: center;
}

.nav-items:last-child{
    margin-left: auto;
}

nav a{
    height:100%;
    padding: 0 30px;
    text-decoration: none;
    display: flex;
    align-items: center;
    color: black;
}

.logo{
  width:100px;
}
</style>