// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import HomePage from '../views/HomePage.vue'
import LoginPage from '../views/LoginPage.vue'
import ManageStudentEnrollmentPage from '../views/ManageStudentEnrollmentPage.vue'
import ComponentMarksPage from '../views/ComponentMarksPage.vue'
import AddFinalExamMarksPage from '../views/AddFinalExamMarksPage.vue'
import ViewFullMarkBreakdownPage from '../views/ViewFullMarkBreakdownPage.vue'
import AddPerformanceTrendPage from '../views/AddPerformanceTrendPage.vue'

const routes = [
  { path: '/home', name: 'Home', component: HomePage },
  { path: '/', name: 'Login', component: LoginPage },
  { path: '/enroll', name: 'Enroll', component: ManageStudentEnrollmentPage },
  { path: '/manage-component-marks', name: 'ComponentMarks', component: ComponentMarksPage },
  { path: '/add-final-exam-marks', name: 'AddFinalExamMarks', component: AddFinalExamMarksPage },
  { path: '/mark-breakdown', name: 'ViewFullMarkBreakdown', component: ViewFullMarkBreakdownPage },
  { path: '/performance-trend', name: 'AddPerformanceTrend', component: AddPerformanceTrendPage }
]



const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

// router.beforeEach((to, from, next) => {
//   //const sessionData = sessionStorage.getItem('utmwebfc_session'); 

//   //if (!sessionData && to.path !== '/login') {
//     next({ path: '/home' });
//   //} else {
//   //  next();
//   //}
// });

export default router