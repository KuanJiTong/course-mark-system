// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
// import HomePage from '../views/HomePage.vue'
import LoginPage from '../views/LoginPage.vue'

const routes = [
//   { path: '/', name: 'Home', component: HomePage },
  { path: '/', name: 'Login', component: LoginPage },
]



const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

// router.beforeEach((to, from, next) => {
//   const sessionData = sessionStorage.getItem('utmwebfc_session'); 

//   if (!sessionData && to.path !== '/login') {
//     next({ path: '/login' });
//   } else {
//     next();
//   }
// });

export default router