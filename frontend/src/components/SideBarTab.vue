<template>
  <div class="nav nav-pills flex-column mb-auto">
    <router-link 
        :to="item.link" 
        class="nav-link" 
        :class="{ active: isActive }"
      >
      <div class="d-flex align-items-center" >
        <span v-if="item.icon" class="icon" :class="item.icon"></span>
        <span>{{ item.name }}</span>
      </div>
    </router-link>
  </div>
</template>
  
<script>
export default {
  name: 'SideBarTab',
  props: {
    item: {
      type: Object,
      required: true, 
    },
    isActive: Boolean,
  },
  methods: {
   
  }
};
</script>
  
<style scoped>
.icon {
  vertical-align: middle;
  margin-bottom: 9px;
  margin-right: 15px;
  width: 1.2em;
  height: 1.2em;
  font-size: 20px;
}

.nav-link {
  color:#000000; 
}

.router-link-exact-active,.router-link-exact-active:hover {
  background-color: #0d6efd!important; 
  color: #ffffff;
}

.nav-link:hover{
  color: #ffffff;
  background-color: #b3e0ff;
}

.ml-auto {
  display: flex;               
  align-items: center;         
  justify-content: flex-end;   
}
</style>
  
  